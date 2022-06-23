<?php


namespace okcoder\think\jwt;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use okcoder\think\jwt\exceptions\AudienceVerifyFailException;
use okcoder\think\jwt\exceptions\IssuerVerifyFailException;
use okcoder\think\jwt\exceptions\JtiVerifyFailException;
use okcoder\think\jwt\exceptions\SignKeyVerifyFailException;
use okcoder\think\jwt\exceptions\SubjectVerifyFailException;
use okcoder\think\jwt\exceptions\TokenExpiredException;
use okcoder\think\jwt\exceptions\TokenLogoutOffException;
use okcoder\think\jwt\exceptions\TokenParseFailException;
use okcoder\think\jwt\parser\Cookie;
use okcoder\think\jwt\parser\Header;
use okcoder\think\jwt\parser\Param;
use okcoder\think\jwt\parser\Parser;
use think\facade\Cache;
use think\facade\Config;
use think\Request;

class Jwt
{
    /**
     * 得到配置对象
     * @return Configuration
     */
    private static function getJwtConfig(): Configuration
    {
        return Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(Config::get('jwt.jti')));
    }

    /**
     * @param integer|array|string $data 往token里面传入的数据
     * @return string|boolean 执行成功返回token字符串 ,失败返回false
     */
    public static function create($data, int $exp = 0)
    {
        try {
            $config = self::getJwtConfig();
            $now    = new DateTimeImmutable('now', new DateTimeZone(Config::get('jwt.timezone')));
            return $config->builder()
                //主题
                ->relatedTo(Config::get('jwt.sub'))
                //签发人
                ->issuedBy(Config::get('jwt.iss'))
                //接收人  // canOnlyBeUsedBy方法在4.x中将会被移除被permittedFor替代
                ->permittedFor(...array_filter(Config::get('jwt.aud', []), 'is_string'))
                //唯一标志
                ->identifiedBy(Config::get('jwt.jti'))
                //签发时间
                ->issuedAt($now)
                //生效时间（立即生效:签发时间减一秒）
                ->canOnlyBeUsedAfter($now->modify('-1 second'))
                //过期时间
                ->expiresAt($now->modify("+" . ($exp ?: Config::get('jwt.exp')) . " second"))
                //存在token中的数据   // with方法在4.x中将会被移除被withClaim替代
                ->withClaim('_thinkJwt', json_encode($data, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE))
                //签名
                ->getToken($config->signer(), $config->signingKey())
                ->toString();
        } catch (\Exception $e) {
            return false;
        }

    }


    public static function getToken(): string
    {
        $chains = [
            'header' => new Header(),
            'cookie' => new Cookie(),
            'param'  => new Param()
        ];

        $mode = Config::get('jwt.token_mode');

        foreach ($mode as $key => $chain) {
            if (isset($chains[$chain])) {
                $parser = $chains[$chain];
                return invoke(function (Request $request) use ($parser) {
                    return $parser->parse($request);
                });
            }
        }
    }

    /**
     * 解析token
     * @param $token
     * @return array
     * @throws AudienceVerifyFailException
     * @throws IssuerVerifyFailException
     * @throws JtiVerifyFailException
     * @throws SignKeyVerifyFailException
     * @throws SubjectVerifyFailException
     * @throws TokenExpiredException
     * @throws TokenLogoutOffException
     * @throws TokenParseFailException
     */
    public static function parse($token): array
    {
        $config = self::getJwtConfig();


        //注销token逻辑
        $delete_token = Cache::get(Config::get('jwt.delete_key')) ?: [];
        if (in_array($token, $delete_token)) {
            //token已被注销
            throw new TokenLogoutOffException();
        }

        //token解析异常必须要用try catch抓取 当JWT不是字符串或无效时会抛出异常
        try {
            $token = $config->parser()->parse($token);
        } catch (\Exception $e) {
            throw new TokenParseFailException();
        }

        //验证声明iss是否列为预期值
        $issued = new IssuedBy(Config::get('jwt.iss'));
        if (!$config->validator()->validate($token, $issued)) {
            throw new IssuerVerifyFailException();
        }


        //验证声明是否aud包含预期值
        $aud_list = array_filter(Config::get('jwt.aud', []), 'is_string');
        foreach ($aud_list as $aud) {
            if (!$config->validator()->validate($token, new PermittedFor($aud))) {
                throw new AudienceVerifyFailException();
            }
        }

        //验证声明是否jti与预期值匹配
        $jti = new IdentifiedBy(Config::get('jwt.jti'));
        if (!$config->validator()->validate($token, $jti)) {
            throw new JtiVerifyFailException();
        }

        //验证声明是否sub与预期值匹配
        $sub = new RelatedTo(Config::get('jwt.sub'));
        if (!$config->validator()->validate($token, $sub)) {
            throw new SubjectVerifyFailException();
        }

        //验证令牌是否使用预期的签名者和密钥签名
        $sign = new SignedWith($config->signer(), $config->signingKey());
        if (!$config->validator()->validate($token, $sign)) {
            throw new SignKeyVerifyFailException();
        }

        //验证声明iat, nbf, 和exp(支持 leeway 配置)
        $now          = new SystemClock(new DateTimeZone(Config::get('jwt.timezone')));
        $looseValidAt = new LooseValidAt($now);
        if (!$config->validator()->validate($token, $looseValidAt)) {
            throw new TokenExpiredException();
        }

        //从token中取出存储的数据
        return json_decode($token->claims()->get('_thinkJwt'), true);
    }

    /**
     * @param string $token 需要注销的token
     * @return void
     */
    public static function logout(string $token)
    {

        //取缓存中注销的token数组
        $delete_token = Cache::get(Config::get('jwt.delete_key')) ?: [];

        //把传递过来的token再存入缓存
        $delete_token[] = $token;

        //再次把新的缓存数据重新存入缓存中，缓存时间必须大于等于jwt生成时的有效期,否则注销不成功
        Cache::set(Config::get('jwt.delete_key'), $delete_token, Config::get('jwt.exp'));
    }
}