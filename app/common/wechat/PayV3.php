<?php

/**
 * 微信支付
 * User: Yacon
 * Date: 2022-10-10
 * Time: 14:32
 */

namespace app\common\wechat;

use Exception;
use WeChatPay\Builder;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Exception\InvalidArgumentException;
use WeChatPay\Formatter;
use WeChatPay\Util\PemUtil;

class PayV3
{

  public function __construct($appId, $mchId, $mchKey, $serial)
  {
    $this->appId = $appId;
    $this->mchId = $mchId;
    $this->mchKey = $mchKey;
    $this->notifyUrl_recharge =  '';
    $this->serial = $serial;
    $this->unifiedOrderUrl = 'v3/pay/transactions/jsapi';
    $this->refundUrl = 'v3/refund/domestic/refunds';
    $this->orderQueryUrl = 'v3/pay/transactions/out-trade-no/{out_trade_no}';
    $this->logPath = LOG_PATH;
    $this->privateKeyPath = 'file:///' . APP_PATH . 'common' . DS . 'certs' . DS . 'apiclient_key.pem';
    $this->certPath = 'file:///' . APP_PATH . 'common' . DS . 'certs' . DS . 'cert.pem';
    $this->client =  $this->getClient();
  }


  /**
   * 创建客户端实例
   */
  public function getClient()
  {
    try {
      // 商户API私钥
      $merchantPrivateKeyInstance = Rsa::from($this->privateKeyPath, Rsa::KEY_TYPE_PRIVATE);

      // 微信支付平台证书
      $platformPublicKeyInstance = Rsa::from($this->certPath, Rsa::KEY_TYPE_PUBLIC);

      // 获取证书序列号
      $platformCertificateSerial = PemUtil::parseCertificateSerialNo($this->certPath);

      return Builder::factory([
        'mchid'      => $this->mchId,
        'serial'     => $this->serial,
        'privateKey' => $merchantPrivateKeyInstance,
        'certs'      => [
          $platformCertificateSerial => $platformPublicKeyInstance,
        ]
      ]);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * 下单
   * @param $outTradeNo 订单号
   * @param $fee 订单金额
   * @param $userOpenId 用户openid 在h5支付中不使用
   * @param $body 订单body
   * @param $attach 订单携带标识
   * @param $pay_type 支付类型 - 1 h5支付 2公众号支付or小程序支付 3app支付
   * @param $notifyUrl 支付callback 地址
   * @return array|bool
   */
  public function order($outTradeNo, $fee, $userOpenId = '', $body, $attach = '', $pay_type, $notifyUrl)
  {
    try {

      $requestData = [
        'mchid'        => $this->mchId,
        'out_trade_no' => $outTradeNo,
        'appid'        => $this->appId,
        'description'  => $body,
        'attach'  => $attach,
        'notify_url'   => $notifyUrl,
        'amount'       => [
          'total'    => $fee * 100,
          'currency' => 'CNY'
        ],
        'payer' => [
          'openid' => $userOpenId
        ]
      ];


      $response = $this->client
        ->chain($this->unifiedOrderUrl)
        ->post(['json' => $requestData]);

      if ($response->getStatusCode() != 200) {
        throw new Exception("支付失败");
      }
      $response = $response->getBody()->getContents();
      $response = objToArray(json_decode($response));
      $prepay_id = $response['prepay_id'];

      $params = [
        'appId'     => $this->appId,
        'timeStamp' => (string)Formatter::timestamp(),
        'nonceStr'  => Formatter::nonce(),
        'package'   => 'prepay_id=' . $prepay_id,
      ];

      $params += ['paySign' => Rsa::sign(
        Formatter::joinedByLineFeed(...array_values($params)),
        Rsa::from($this->privateKeyPath)
      ), 'signType' => 'RSA'];

      return $params;
    } catch (\Exception $e) {
      // 进行错误处理
      echo $e->getMessage(), PHP_EOL;
      if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
        $r = $e->getResponse();
        echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
        echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
      }
      echo $e->getTraceAsString(), PHP_EOL;
    }
  }

  /**
   * 退款
   * @param $transactionId 微信支付订单号
   * @param $outRefundNo 退款单号
   * @param $fee - 订单总金额
   * @param $refundFee - 退款金额
   * @return bool
   * @throws Exception
   */
  public function refund($outRefundNo, $transactionId, $fee, $refundFee)
  {
    try {
      $requestData = [
        'out_trade_no' => $transactionId,
        'out_refund_no'  => $outRefundNo,
        'amount'         => [
          'refund'   => $refundFee * 100,
          'total'    => $fee * 100,
          'currency' => 'CNY',
        ],
      ];

      $response = $this->client
        ->chain($this->refundUrl)
        ->post(['json' => $requestData]);

      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * 订单查询
   * @param $transactionId 微信支付订单号
   */
  public function query($outTradeNo)
  {
    try {

      $response = $this->client
        ->chain($this->orderQueryUrl)
        ->get([
          "query" => ['mchid' => $this->mchId],
          "out_trade_no" => $outTradeNo
        ]);

      $body = $response->getBody()->getContents();
      $body = json_decode($body);
      $body = objToArray($body);
      $tradeState = $body['trade_state'];

      // 支付成功
      if ($tradeState == 'SUCCESS' || $tradeState == 'REFUND') {
        return true;
      }

      return false;
    } catch (Exception $e) {
      // 异常错误处理
      echo $e->getMessage(), PHP_EOL;
      if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
        $r = $e->getResponse();
        echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
        echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
      }
      echo $e->getTraceAsString(), PHP_EOL;
    }
  }

  /**
   * 支付回调处理
   */
  public function handleNotify($request, $headers)
  {
    $inWechatpaySignature = $headers['Wechatpay-Signature'];
    $inWechatpayTimestamp = $headers['Wechatpay-Timestamp'];
    $inWechatpaySerial = $headers['Wechatpay-Serial'];
    $inWechatpayNonce = $headers['Wechatpay-Nonce'];
    $inBody = $request;

    $apiv3Key = $this->mchKey; // 在商户平台上设置的APIv3密钥

    // 根据通知的平台证书序列号，查询本地平台证书文件，
    $platformPublicKeyInstance = Rsa::from($this->certPath, Rsa::KEY_TYPE_PUBLIC);

    // 检查通知时间偏移量，允许5分钟之内的偏移
    $timeOffsetStatus = 300 >= abs(Formatter::timestamp() - (int)$inWechatpayTimestamp);
    $verifiedStatus = Rsa::verify(
      // 构造验签名串
      Formatter::joinedByLineFeed($inWechatpayTimestamp, $inWechatpayNonce, $inBody),
      $inWechatpaySignature,
      $platformPublicKeyInstance
    );

    if ($timeOffsetStatus && $verifiedStatus) {
      // 转换通知的JSON文本消息为PHP Array数组
      $inBodyArray = (array)json_decode($inBody, true);
      // 使用PHP7的数据解构语法，从Array中解构并赋值变量
      ['resource' => [
        'ciphertext'      => $ciphertext,
        'nonce'           => $nonce,
        'associated_data' => $aad
      ]] = $inBodyArray;
      // 加密文本消息解密
      $inBodyResource = AesGcm::decrypt($ciphertext, $apiv3Key, $nonce, $aad);
      // 把解密后的文本转换为PHP Array数组
      $inBodyResourceArray = (array)json_decode($inBodyResource, true);
      return $inBodyResourceArray;
    }
  }

  /**
   * 下载证书
   */
  public function  downloadCert()
  {
    try {
      $params = [
        'method' => 'GET',
        'url' => 'https://api.mch.weixin.qq.com/v3/certificates',
        'timestamp' => (string)Formatter::timestamp(),
        'nonce' => Formatter::nonce(),
        'body' => ''
      ];
      $headers = self::getRequestHeader($params);
      $result = self::curl_get($params['url'], $headers);
      $result = json_decode($result, true);
      $aesUtil = new AesUtil($this->mchKey);
      $cert = $aesUtil->decryptToString($result['data'][0]['encrypt_certificate']['associated_data'], $result['data'][0]['encrypt_certificate']['nonce'], $result['data'][0]['encrypt_certificate']['ciphertext']);
      file_put_contents($this->certPath, $cert);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * 发送GET请求
   */
  public static function curl_get($url, $headers = array())
  {
    $info = curl_init();
    curl_setopt($info, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($info, CURLOPT_HEADER, 0);
    curl_setopt($info, CURLOPT_NOBODY, 0);
    curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($info, CURLOPT_SSL_VERIFYHOST, false);
    //设置header头
    curl_setopt($info, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($info, CURLOPT_URL, $url);
    $output = curl_exec($info);
    curl_close($info);
    return $output;
  }

  /**
   * 生成签名
   */
  public function markSign($params)
  {
    $timestamp = $params['timestamp'];
    $nonce = $params['nonce'];
    $url_parts = parse_url($params['url']);
    $canonical_url  = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));

    $message = $params['method'] . "\n" . $canonical_url  . "\n" . $timestamp . "\n" . $nonce . "\n" . $params['body'] . "\n";

    openssl_sign($message, $raw_sign, openssl_pkey_get_private($this->privateKeyPath), 'sha256WithRSAEncryption');
    $sign = base64_encode($raw_sign);

    return $sign;
  }

  /**
   * 生成请求头
   */
  public function getRequestHeader($params)
  {
    $sign = self::markSign($params);

    $schema = 'WECHATPAY2-SHA256-RSA2048';

    $token = sprintf(
      '%s mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
      $schema,
      $this->mchId,
      $params['nonce'],
      $params['timestamp'],
      $this->serial,
      $sign
    );

    $headers = [
      'Accept: application/json',
      'User-Agent: */*',
      'Content-Type: application/json; charset=utf-8',
      'Authorization: ' . $token,
    ];

    return $headers;
  }
}

class AesUtil
{
  /**
   * AES key
   *
   * @var string
   */
  private $aesKey;

  const KEY_LENGTH_BYTE = 32;
  const AUTH_TAG_LENGTH_BYTE = 16;

  /**
   * Constructor
   */
  public
  function __construct($aesKey)
  {
    if (strlen($aesKey) != self::KEY_LENGTH_BYTE) {
      throw new InvalidArgumentException('无效的ApiV3Key，长度应为32个字节');
    }
    $this->aesKey = $aesKey;
  }

  /**
   * Decrypt AEAD_AES_256_GCM ciphertext
   *
   * @param string    $associatedData     AES GCM additional authentication data
   * @param string    $nonceStr           AES GCM nonce
   * @param string    $ciphertext         AES GCM cipher text
   *
   * @return string|bool      Decrypted string on success or FALSE on failure
   */
  public
  function decryptToString($associatedData, $nonceStr, $ciphertext)
  {
    $ciphertext = \base64_decode($ciphertext);
    if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
      return false;
    }

    // ext-sodium (default installed on >= PHP 7.2)
    if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
      return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->aesKey);
    }

    // ext-libsodium (need install libsodium-php 1.x via pecl)
    if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
      return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->aesKey);
    }

    // openssl (PHP >= 7.1 support AEAD)
    if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
      $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
      $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);

      return \openssl_decrypt(
        $ctext,
        'aes-256-gcm',
        $this->aesKey,
        \OPENSSL_RAW_DATA,
        $nonceStr,
        $authTag,
        $associatedData
      );
    }

    throw new \RuntimeException('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
  }
}
