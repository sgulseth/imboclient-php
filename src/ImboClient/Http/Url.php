<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Http;

use Guzzle\Http\Url as GuzzleUrl;

/**
 * Base URL class
 *
 * @package Client\Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Url extends GuzzleUrl {
    /**
     * Private key of a user
     *
     * @var string
     */
    private $privateKey;

    /**
     * Factory method
     *
     * @param string $url URL as a string
     * @param string $privateKey Optional private key
     * @param string $publicKey Optional public key
     * @return Url
     */
    public static function factory($url, $privateKey = null, $publicKey = null) {
        $url = parent::factory($url);
        $user = self::getUserFromUrl($url->getPath());

        if ($privateKey) {
            $url->setPrivateKey($privateKey);
        }

        if ($publicKey && $user !== $publicKey) {
            $url->getQuery()->set('publicKey', $publicKey);
        }

        return $url;
    }

    /**
     * Get the user part of a given URL
     *
     * @return string|null
     */
    public function getUserFromUrl($url) {
        if (preg_match('#/users/(?<user>[^./]+)#', $url, $match)) {
            return $match['user'];
        }

        return null;
    }

    /**
     * Return the URL as a string
     *
     * @return string
     */
    public function __toString() {
        $asString = parent::__toString();

        if ($this->privateKey) {
            $accessToken = hash_hmac('sha256', urldecode($asString), $this->privateKey);

            $url = GuzzleUrl::factory($asString);
            $url->getQuery()->set('accessToken', $accessToken);

            return (string) $url;
        }

        return $asString;
    }

    /**
     * Get the public key part of a URL
     *
     * @return string|null
     */
    public function getPublicKey() {
        return $this->getQuery()->get('publicKey') ?: $this->getUser();
    }

    /**
     * Get the user part of a URL
     *
     * @return string|null
     */
    public function getUser() {
        return self::getUserFromUrl($this->getPath());
    }

    /**
     * Set the private key
     *
     * @param string $privateKey The private key to use when appending access tokens to the URL's
     */
    public function setPrivateKey($privateKey) {
        $this->privateKey = $privateKey;
    }
}
