<?php
require_once 'Crypt/RSA.php';

class MagicSig {
  define( 'MAGIC_SIG_NS', 'http://salmon-protocol.org/ns/magic-env');

  function get_public_sig($user_id) {
    if ($sig = get_user_meta($user_id, "magic_sig_public_key")) {
      return $sig[0];
    } else {
      MagicSig::generate_key_pair($user_id);

      $sig = get_user_meta($user_id, "magic_sig_public_key");
      return $sig[0];
    }
  }

  //Generates the pair keys
  function generate_key_pair($user_id) {
    $rsa = new Crypt_RSA();

    $keypair = $rsa->createKey();

    update_user_meta($user_id, "magic_sig_public_key", $keypair['publickey']);
    update_user_meta($user_id, "magic_sig_private_key", $keypair['privatekey']);
  }

  function base64_url_encode($input) {
    return strtr(base64_encode($input), '+/', '-_');
  }

  function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
  }

  public function to_string($key) {
    $public_key = new Crypt_RSA();
    $public_key->loadKey($key, CRYPT_RSA_PRIVATE_FORMAT_PKCS1);

    $mod = MagicSig::base64_url_encode($public_key->modulus->toBytes());
    $exp = MagicSig::base64_url_encode($public_key->exponent->toBytes());

    return 'RSA.' . $mod . '.' . $exp . $private_exp;
  }

  function parse($text) {
    $dom = DOMDocument::loadXML($text);
    return $this->fromDom($dom);
  }

  function from_dom($dom) {
    $env_element = $dom->getElementsByTagNameNS(MagicEnvelope::NS, 'env')->item(0);
    if (!$env_element) {
      $env_element = $dom->getElementsByTagNameNS(MagicEnvelope::NS, 'provenance')->item(0);
    }

    if (!$env_element) {
      return false;
    }

    $data_element = $env_element->getElementsByTagNameNS(MagicEnvelope::NS, 'data')->item(0);
    $sig_element = $env_element->getElementsByTagNameNS(MagicEnvelope::NS, 'sig')->item(0);
    return array(
      'data' => preg_replace('/\s/', '', $data_element->nodeValue),
      'data_type' => $data_element->getAttribute('type'),
      'encoding' => $env_element->getElementsByTagNameNS(MAGIC_SIG_NS, 'encoding')->item(0)->nodeValue,
      'alg' => $env_element->getElementsByTagNameNS(MAGIC_SIG_NS, 'alg')->item(0)->nodeValue,
      'sig' => preg_replace('/\s/', '', $sig_element->nodeValue),
    );
  }
}
?>