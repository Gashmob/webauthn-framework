<?php

declare(strict_types=1);

namespace Webauthn\Bundle\Controller;

use Assert\Assertion;
use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialUserEntity;

final class AssertionResponseController
{
    public function __construct(
        private HttpMessageFactoryInterface $httpMessageFactory,
        private PublicKeyCredentialLoader $publicKeyCredentialLoader,
        private AuthenticatorAssertionResponseValidator $assertionResponseValidator,
        private string $sessionParameterName,
        private LoggerInterface $logger,
        private CacheItemPoolInterface $cacheItemPool
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $psr7Request = $this->httpMessageFactory->createRequest($request);
            Assertion::eq('json', $request->getContentType(), 'Only JSON content type allowed');
            $content = $request->getContent();
            Assertion::string($content, 'Invalid data');
            $publicKeyCredential = $this->publicKeyCredentialLoader->load($content);
            $response = $publicKeyCredential->getResponse();
            Assertion::isInstanceOf($response, AuthenticatorAssertionResponse::class, 'Invalid response');
            $item = $this->cacheItemPool->getItem($this->sessionParameterName);
            if (! $item->isHit()) {
                throw new InvalidArgumentException('Unable to find the public key credential request options');
            }
            $data = $item->get();
            Assertion::isArray($data, 'Unable to find the public key credential request options');
            Assertion::keyExists($data, 'options', 'Unable to find the public key credential request options');
            $publicKeyCredentialRequestOptions = $data['options'];
            Assertion::isInstanceOf(
                $publicKeyCredentialRequestOptions,
                PublicKeyCredentialRequestOptions::class,
                'Unable to find the public key credential request options'
            );
            Assertion::keyExists($data, 'userEntity', 'Unable to find the public key credential request options');
            $userEntity = $data['userEntity'];
            Assertion::nullOrIsInstanceOf(
                $userEntity,
                PublicKeyCredentialUserEntity::class,
                'Unable to find the public key credential request options'
            );
            $userEntityId = $userEntity !== null ? $userEntity->getId() : null;
            $this->assertionResponseValidator->check(
                $publicKeyCredential->getRawId(),
                $response,
                $publicKeyCredentialRequestOptions,
                $psr7Request,
                $userEntityId
            );

            return new JsonResponse([
                'status' => 'ok',
                'errorMessage' => '',
            ]);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());

            return new JsonResponse([
                'status' => 'failed',
                'errorMessage' => $throwable->getMessage(),
            ], 400);
        }
    }
}