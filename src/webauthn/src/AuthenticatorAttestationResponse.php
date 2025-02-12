<?php

declare(strict_types=1);

namespace Webauthn;

use Webauthn\AttestationStatement\AttestationObject;

/**
 * @see https://www.w3.org/TR/webauthn/#authenticatorattestationresponse
 */
class AuthenticatorAttestationResponse extends AuthenticatorResponse
{
    /**
     * @param string[] $transports
     */
    public function __construct(
        CollectedClientData $clientDataJSON,
        private readonly AttestationObject $attestationObject,
        private readonly array $transports = []
    ) {
        parent::__construct($clientDataJSON);
    }

    public function getAttestationObject(): AttestationObject
    {
        return $this->attestationObject;
    }

    /**
     * @return string[]
     */
    public function getTransports(): array
    {
        return $this->transports;
    }
}
