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

namespace Somoza\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Sign2PayException
 * @author Gabriel Somoza <gabriel.somoza@cu.be>
 */
final class Sign2PayException extends IdentityProviderException
{
    /** @var ResponseInterface */
    private $responseObject;

    /**
     * Sign2PayException constructor.
     * @param ResponseInterface $response
     * @param int $message
     * @param array $data
     */
    public function __construct(ResponseInterface $response, $message, array $data)
    {
        $this->responseObject = $response;
        parent::__construct((string) $message, (int) $response->getStatusCode(), $data);
    }

    /**
     * fromResponse
     * @param ResponseInterface $response
     * @param $data
     * @return static
     */
    public static function fromResponse(ResponseInterface $response, array $data = [])
    {
        $title = !empty($data['error']) ? $data['error'] : '';
        $description = !empty($data['error_description']) ? ': ' . $data['error_description'] : '';
        return new static($response, $title . $description, $data);
    }

    /**
     * @return ResponseInterface
     */
    public function getResponseObject()
    {
        return $this->responseObject;
    }
}
