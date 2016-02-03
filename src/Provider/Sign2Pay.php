<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by one or more
 * individuals and is licensed under the MIT license. For more information,
 * see <https://github.com/gabrielsomoza/oauth2-sign2pay>.
 */

namespace Somoza\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Somoza\OAuth2\Client\Provider\Exception\Sign2PayException;

/**
 * Class Sign2Pay
 * @author Gabriel Somoza <gabriel.somoza@cu.be>
 */
final class Sign2Pay extends AbstractProvider
{
    /**
     * Sign2Pay Payment scope
     *
     * @const string
     */
    const SCOPE_PAYMENT = 'payment';

    /**
     * Sign2Pay base URL.
     *
     * @const string
     */
    const BASE_SIGN2PAY_URL = 'https://app.sign2pay.com';

    /**
     * Returns the base URL for authorizing the Sign2Pay client.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseSign2PayUrl() . '/oauth/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        $queryParams = !empty($params) ? '?' . http_build_query($params) : '';
        return $this->getBaseSign2PayUrl() . '/oauth/token' . $queryParams;
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return null;
    }

    /**
     * checkAccessTokenValid
     * @param $token
     * @return ResponseInterface
     */
    public function checkAccessTokenValid($token)
    {
        $request = $this->getAuthenticatedRequest(
            'GET',
            $this->getAccessTokenUrl([
                'client_id' => $this->clientId,
                'scope' => 'payment',
            ]),
            $token,
            ['headers' => $this->getDefaultHeaders()]
        );
        $response = $this->getResponse($request);
        return !empty($response['status']) && $response['status'] == 'ok';
    }

    /**
     * getHeaders
     * @param null $token
     * @return array
     */
    public function getHeaders($token = null)
    {
        return array_merge(
            $this->getDefaultHeaders(),
            $this->getAuthorizationHeaders($token)
        );
    }


    /**
     * getDefaultHeaders
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return [
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate'
        ];
    }


    /**
     * getAuthorizationParameters
     * @param array $options
     * @return array
     */
    protected function getAuthorizationParameters(array $options)
    {
        $params = parent::getAuthorizationParameters($options);
        if (isset($options['ref_id'])) {
            $params['ref_id'] = $options['ref_id'];
        }
        if (isset($options['amount'])) {
            $params['amount'] = $options['amount'];
        }

        return $params;
    }


    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [static::SCOPE_PAYMENT]; // none of the others are accepted
    }

    /**
     * Checks a provider response for errors.
     *
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @throws Sign2PayException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            throw Sign2PayException::fromResponse($response, $data);
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }

    /**
     * getAuthorizationHeaders
     * @param null $token
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        $headers = [
            'Authorization' => empty($token) ?
                'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret) :
                'Bearer ' . $token
        ];
        return $headers;
    }

    /**
     * getBaseSign2PayUrl
     *
     * @return string
     */
    private function getBaseSign2PayUrl()
    {
        return static::BASE_SIGN2PAY_URL;
    }
}
