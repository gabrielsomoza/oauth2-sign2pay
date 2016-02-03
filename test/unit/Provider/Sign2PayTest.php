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


namespace Somoza\OAuth2Test\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Somoza\OAuth2\Client\Provider\Exception\Sign2PayException;
use Somoza\OAuth2\Client\Provider\Sign2Pay;
use Somoza\OAuth2Test\Client\BaseTestCase;
use Mockery as m;

/**
 * Class Sign2PayTest
 * @author Gabriel Somoza <gabriel.somoza@cu.be>
 */
class Sign2PayTest extends BaseTestCase
{
    /** @var Sign2Pay */
    protected $provider;

    /**
     * setUp
     * @return void
     */
    public function setUp()
    {
        $this->provider = new Sign2Pay();
    }

    /**
     * testGetBaseAuthorizationUrl
     * @return void
     */
    public function testGetBaseAuthorizationUrl()
    {
        $this->assertEquals('https://app.sign2pay.com/oauth/authorize', $this->provider->getBaseAuthorizationUrl());
    }

    /**
     * testGetBaseAccessTokenUrl
     * @return void
     */
    public function testGetBaseAccessTokenUrlWithEmptyParams()
    {
        $this->assertEquals('https://app.sign2pay.com/oauth/token', $this->provider->getBaseAccessTokenUrl([]));
    }

    /**
     * testGetBaseAccessTokenUrl
     * @return void
     */
    public function testGetBaseAccessTokenUrlWithParams()
    {
        $this->assertEquals(
            'https://app.sign2pay.com/oauth/token?foo=bar&baz=1234',
            $this->provider->getBaseAccessTokenUrl([
            'foo' => 'bar',
            'baz' => 1234,
            ])
        );
    }

    /**
     * testGetResourceOwnerDetailsUrlReturnsNull
     * @return void
     */
    public function testGetResourceOwnerDetailsUrlReturnsNull()
    {
        /** @var AccessToken|m\Mock $token */
        $token = m::mock(AccessToken::class);
        $this->assertNull($this->provider->getResourceOwnerDetailsUrl($token));
    }

    /**
     * testGetDefaultHeaders
     * @return void
     */
    public function testGetDefaultHeaders()
    {
        $defaultHeaders = $this->invoke($this->provider, 'getDefaultHeaders');
        $this->assertNotEmpty($defaultHeaders);
        $this->assertArrayHasKey('Accept', $defaultHeaders);
        $this->assertArrayHasKey('Accept-Encoding', $defaultHeaders);
    }

    /**
     * testGetDefaultScopes
     * @return void
     */
    public function testGetDefaultScopes()
    {
        $defaultScopes = $this->invoke($this->provider, 'getDefaultScopes');
        $this->assertNotEmpty($defaultScopes);
        $this->assertContains('payment', $defaultScopes);
    }

    /**
     * testCreateResourceOwnerReturnsNull
     * @return void
     */
    public function testCreateResourceOwnerReturnsNull()
    {
        $token = m::mock(AccessToken::class);
        $result = $this->invoke($this->provider, 'createResourceOwner', [[], $token]);
        $this->assertNull($result);
    }

    /**
     * testGetAuthorizationHeaders
     * @param $token
     * @param $expected
     * @return void
     * @dataProvider getAuthorizationHeadersProvider
     */
    public function testGetAuthorizationHeaders($token, $expected)
    {
        $result = $this->invoke($this->provider, 'getAuthorizationHeaders', [$token]);
        $this->assertArrayHasKey('Authorization', $result);
        $this->assertStringStartsWith($expected, $result['Authorization']);
    }

    /**
     * testGetAuthorizationHeadersUsesClientIdAndSecretForBasicAuth
     * @return void
     * @depends testGetAuthorizationHeaders
     */
    public function testGetAuthorizationHeadersUsesClientIdAndSecretForBasicAuth()
    {
        $client = new Sign2Pay([
            'clientId' => '123',
            'clientSecret' => '987',
        ]);
        $result = $this->invoke($client, 'getAuthorizationHeaders');
        $this->assertEquals('Basic ' . base64_encode('123:987'), $result['Authorization']);
    }

    /**
     * getAuthorizationHeadersProvider
     * @return array
     */
    public function getAuthorizationHeadersProvider()
    {
        return [
            [null, 'Basic '],
            ['test', 'Bearer test']
        ];
    }

    /**
     * testCheckResponse
     * @param array $data
     * @param bool $exception
     * @dataProvider checkResponseProvider
     */
    public function testCheckResponse(array $data, $exception = false)
    {
        $response = m::mock(ResponseInterface::class, [
            'getStatusCode' => 200,
        ]);
        if ($exception) {
            $this->setExpectedException(Sign2PayException::class);
        }
        $this->invoke($this->provider, 'checkResponse', [$response, $data]);
    }

    /**
     * checkResponseProvider
     * @return array
     */
    public function checkResponseProvider()
    {
        return [
            [[]],
            [['error' => "foo"], true],
            [['error' => "foo", 'error_description' => 'bar'], true],
        ];
    }

    /**
     * testGetAuthorizationParameters
     * @return void
     */
    public function testGetAuthorizationParameters()
    {
        $this->markTestIncomplete();
    }

    /**
     * testGetHeaders
     * @return void
     */
    public function testGetHeaders()
    {
        $this->markTestIncomplete();
    }

    /**
     * testCheckAccessTokenValid
     * @return void
     */
    public function testCheckAccessTokenValid()
    {
        $this->markTestIncomplete();
    }
}
