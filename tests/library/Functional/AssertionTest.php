<?php

declare(strict_types=1);

namespace Webauthn\Tests\Functional;

use ParagonIE\ConstantTime\Base64UrlSafe;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Uid\Uuid;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * @internal
 */
final class AssertionTest extends AbstractTestCase
{
    #[Test]
    public function anAssertionCanBeVerified(): void
    {
        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::create(
            base64_decode('G0JbLLndef3a0Iy3S2sSQA8uO4SO/ze6FZMAuPI6+xI=', true)
        )->setTimeout(60000)
            ->setRpId('localhost')
            ->setUserVerification(
                PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED
            )->allowCredential(
                new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    Base64UrlSafe::decode(
                        'eHouz_Zi7-BmByHjJ_tx9h4a1WZsK4IzUmgGjkhyOodPGAyUqUp_B9yUkflXY3yHWsNtsrgCXQ3HjAIFUeZB-w'
                    )
                )
            );
        $publicKeyCredential = $this->getPublicKeyCredentialLoader()
            ->load(
                '{"id":"eHouz_Zi7-BmByHjJ_tx9h4a1WZsK4IzUmgGjkhyOodPGAyUqUp_B9yUkflXY3yHWsNtsrgCXQ3HjAIFUeZB-w","type":"public-key","rawId":"eHouz/Zi7+BmByHjJ/tx9h4a1WZsK4IzUmgGjkhyOodPGAyUqUp/B9yUkflXY3yHWsNtsrgCXQ3HjAIFUeZB+w==","response":{"authenticatorData":"SZYN5YgOjGh0NBcPZHZgW4_krrmihjLHmVzzuoMdl2MBAAAAew","clientDataJSON":"eyJjaGFsbGVuZ2UiOiJHMEpiTExuZGVmM2EwSXkzUzJzU1FBOHVPNFNPX3plNkZaTUF1UEk2LXhJIiwiY2xpZW50RXh0ZW5zaW9ucyI6e30sImhhc2hBbGdvcml0aG0iOiJTSEEtMjU2Iiwib3JpZ2luIjoiaHR0cHM6Ly9sb2NhbGhvc3Q6ODQ0MyIsInR5cGUiOiJ3ZWJhdXRobi5nZXQifQ","signature":"MEUCIEY/vcNkbo/LdMTfLa24ZYLlMMVMRd8zXguHBvqud9AJAiEAwCwpZpvcMaqCrwv85w/8RGiZzE+gOM61ffxmgEDeyhM=","userHandle":null}}'
            );
        static::assertInstanceOf(AuthenticatorAssertionResponse::class, $publicKeyCredential->getResponse());
        $publicKeyCredentialSource = $this->createPublicKeyCredentialSource(
            base64_decode(
                'eHouz/Zi7+BmByHjJ/tx9h4a1WZsK4IzUmgGjkhyOodPGAyUqUp/B9yUkflXY3yHWsNtsrgCXQ3HjAIFUeZB+w==',
                true
            ),
            'foo',
            100,
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            base64_decode(
                'pQECAyYgASFYIJV56vRrFusoDf9hm3iDmllcxxXzzKyO9WruKw4kWx7zIlgg/nq63l8IMJcIdKDJcXRh9hoz0L+nVwP1Oxil3/oNQYs=',
                true
            )
        );
        $publicKeyCredentialSource = $this->getAuthenticatorAssertionResponseValidator()
            ->check(
                $publicKeyCredentialSource,
                $publicKeyCredential->getResponse(),
                $publicKeyCredentialRequestOptions,
                'localhost',
                'foo'
            );
        static::assertSame(123, $publicKeyCredentialSource->getCounter());
    }

    #[Test]
    public function anAssertionWithTokenBindingCanBeVerified(): void
    {
        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::create(
            base64_decode('5rCH1TZzlhWn1ux5QyEFSZlmoYiKJm84FHxJZu1Zk4s=', true)
        )->setTimeout(60000)
            ->setRpId('webauthn.morselli.fr')
            ->setUserVerification(
                PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED
            )->allowCredential(
                new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    base64_decode(
                        '+uZVS9+4JgjAYI49YhdzTgHmbn638+ZNSvC0UtHkWTVS+CtTjnaSbqtzdzijByOAvEAsh+TaQJAr43FRj+dYag==',
                        true
                    )
                )
            );
        $publicKeyCredential = $this->getPublicKeyCredentialLoader()
            ->load(
                '{"id":"-uZVS9-4JgjAYI49YhdzTgHmbn638-ZNSvC0UtHkWTVS-CtTjnaSbqtzdzijByOAvEAsh-TaQJAr43FRj-dYag","type":"public-key","rawId":"+uZVS9+4JgjAYI49YhdzTgHmbn638+ZNSvC0UtHkWTVS+CtTjnaSbqtzdzijByOAvEAsh+TaQJAr43FRj+dYag==","response":{"authenticatorData":"ytRu25lhUyPmYiS9_oq8XVnMLSBjAp3j6bJCBIkJQ7YFAAAAlA","clientDataJSON":"ew0KCSJ0eXBlIiA6ICJ3ZWJhdXRobi5nZXQiLA0KCSJjaGFsbGVuZ2UiIDogIjVyQ0gxVFp6bGhXbjF1eDVReUVGU1psbW9ZaUtKbTg0Rkh4Slp1MVprNHMiLA0KCSJvcmlnaW4iIDogImh0dHBzOi8vd2ViYXV0aG4ubW9yc2VsbGkuZnIiLA0KCSJ0b2tlbkJpbmRpbmciIDogDQoJew0KCQkic3RhdHVzIiA6ICJzdXBwb3J0ZWQiDQoJfQ0KfQ","signature":"MEUCIQCqFeffY4MT0dI95aS4zMiKjEb33zA/xGy3k9LTWjhgXgIgT39F2NoCc7UNLOy9N6Xf6bC4E3j056ZGGrbXcLY4F/A=","userHandle":null}}'
            );
        static::assertInstanceOf(AuthenticatorAssertionResponse::class, $publicKeyCredential->getResponse());
        $publicKeyCredentialSource = $this->createPublicKeyCredentialSource(
            base64_decode(
                '+uZVS9+4JgjAYI49YhdzTgHmbn638+ZNSvC0UtHkWTVS+CtTjnaSbqtzdzijByOAvEAsh+TaQJAr43FRj+dYag==',
                true
            ),
            'foo',
            100,
            Uuid::fromBinary(base64_decode('+KAR84wKTRWABhcRH57cfQ==', true)),
            base64_decode(
                'pQECAyYgASFYIGCFVff/+Igs33wIEwEpwqui12XMF0tof8eDzwZNBX8eIlggcmwcE9F9W5ouuxlzKJbEJIxmUlmRHvBkyDhrqhn7Npw=',
                true
            )
        );
        $publicKeyCredentialSource = $this->getAuthenticatorAssertionResponseValidator()
            ->check(
                $publicKeyCredentialSource,
                $publicKeyCredential->getResponse(),
                $publicKeyCredentialRequestOptions,
                'localhost',
                'foo'
            );
        static::assertSame(148, $publicKeyCredentialSource->getCounter());
    }

    #[Test]
    public function anAssertionWithUserHandleCanBeVerified(): void
    {
        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::create(
            base64_decode('wKlW7S3EENHlcF2NgYhdUJfRJeCvAvlbk+Mllvxo0HA=', true)
        )->setTimeout(60000)
            ->setRpId('spomky-webauthn.herokuapp.com')
            ->setUserVerification(
                PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED
            )->allowCredential(
                new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    base64_decode(
                        'ADqYfFWXiscOCOPCd9OLiBtSGhletNPKlSOELS0Nuwj/uCzf9s3trLUK9ockO8xa8jBAYdKixLZYOAezy0FJiV1bnTCty/LiInWWJlov',
                        true
                    )
                )
            );
        $publicKeyCredential = $this->getPublicKeyCredentialLoader()
            ->load(
                '{"id":"ADqYfFWXiscOCOPCd9OLiBtSGhletNPKlSOELS0Nuwj_uCzf9s3trLUK9ockO8xa8jBAYdKixLZYOAezy0FJiV1bnTCty_LiInWWJlov","type":"public-key","rawId":"ADqYfFWXiscOCOPCd9OLiBtSGhletNPKlSOELS0Nuwj/uCzf9s3trLUK9ockO8xa8jBAYdKixLZYOAezy0FJiV1bnTCty/LiInWWJlov","response":{"authenticatorData":"tIXbbgSILsWHHbR0Fjkl96X4ROZYLvVtOopBWCQoAqpFXFBJyQAAAAAAAAAAAAAAAAAAAAAATgA6mHxVl4rHDgjjwnfTi4gbUhoZXrTTypUjhC0tDbsI_7gs3_bN7ay1CvaHJDvMWvIwQGHSosS2WDgHs8tBSYldW50wrcvy4iJ1liZaL6UBAgMmIAEhWCAIpUDJSoLScguLRDKBEc32v682i6RPjy6SFZnFTBj2QSJYIG8DS0CpphjyFyZB9xyCTrKDsr_S5iX5hhidWLRdP_7B","clientDataJSON":"eyJjaGFsbGVuZ2UiOiJ3S2xXN1MzRUVOSGxjRjJOZ1loZFVKZlJKZUN2QXZsYmstTWxsdnhvMEhBIiwib3JpZ2luIjoiaHR0cHM6Ly9zcG9ta3ktd2ViYXV0aG4uaGVyb2t1YXBwLmNvbSIsInR5cGUiOiJ3ZWJhdXRobi5nZXQifQ","signature":"MEQCIBnVPX8inAXIxXAsMdF6nW6nZJa36G1O+G9JXiauenxBAiBU4MQoRWxiXGn0TcKTkRJafZ58KLqeCJiB2VFAplwPJA==","userHandle":"YWJmYzhmZGYtMDdmNi00NWE5LWFiZWMtZmExOTIyNzViMjc2"}}'
            );
        static::assertInstanceOf(AuthenticatorAssertionResponse::class, $publicKeyCredential->getResponse());
        $publicKeyCredentialSource = $this->createPublicKeyCredentialSource(
            base64_decode(
                'ADqYfFWXiscOCOPCd9OLiBtSGhletNPKlSOELS0Nuwj/uCzf9s3trLUK9ockO8xa8jBAYdKixLZYOAezy0FJiV1bnTCty/LiInWWJlov',
                true
            ),
            'abfc8fdf-07f6-45a9-abec-fa192275b276',
            100,
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            base64_decode(
                'pQECAyYgASFYIAilQMlKgtJyC4tEMoERzfa/rzaLpE+PLpIVmcVMGPZBIlggbwNLQKmmGPIXJkH3HIJOsoOyv9LmJfmGGJ1YtF0//sE=',
                true
            )
        );
        $publicKeyCredentialSource = $this->getAuthenticatorAssertionResponseValidator()
            ->check(
                $publicKeyCredentialSource,
                $publicKeyCredential->getResponse(),
                $publicKeyCredentialRequestOptions,
                'spomky-webauthn.herokuapp.com',
                null
            );
        static::assertSame(1_548_765_641, $publicKeyCredentialSource->getCounter());
    }

    #[Test]
    public function aPreviouslyFixedKeyCanBeVerified(): void
    {
        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::createFromString(
            '{"rp": {"name": "Tuleap","id": "tuleap-web.tuleap-aio-dev.docker"},"user": {"name": "admin","id": "MTAx","displayName": "Site Administrator"},"challenge": "sNZel5OhIwA5vR4wdVkwiGHR6QEnNhYOqi97OHQrc2A","pubKeyCredParams": [{"type": "public-key","alg": -8},{"type": "public-key","alg": -7},{"type": "public-key","alg": -257}],"attestation": "none"}'
        );
        $publicKeyCredential = $this->getPublicKeyCredentialLoader()
            ->load(
                '{"clientExtensionResults": {},"id": "31ivJEY3jmIoxWuGZ7pZjDuBW5n1PAMeG-e0drfhayCzOsuNaCG3PH43i-OebKT0jqY-bAFCEUh1JCCATSPa9N5QIUwwSlUQO9Pb5X1_yXJnY9q7GYfm3LvR4Yk6-HKj4MpBj6cbVOZyoLZQtd2lDEU7pSTcbTZBELQlODGSlbQ","rawId": "31ivJEY3jmIoxWuGZ7pZjDuBW5n1PAMeG-e0drfhayCzOsuNaCG3PH43i-OebKT0jqY-bAFCEUh1JCCATSPa9N5QIUwwSlUQO9Pb5X1_yXJnY9q7GYfm3LvR4Yk6-HKj4MpBj6cbVOZyoLZQtd2lDEU7pSTcbTZBELQlODGSlbQ","response": {"attestationObject": "o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVkBBxawLfvD1MyjfrwvZRZlmxIhDbnhAYq58TqWkGOOpv2oRQAAAAEvwFefgRNH6rEWu1qNuSAqAIDfWK8kRjeOYijFa4ZnulmMO4FbmfU8Ax4b57R2t-FrILM6y41oIbc8fjeL455spPSOpj5sAUIRSHUkIIBNI9r03lAhTDBKVRA709vlfX_Jcmdj2rsZh-bcu9HhiTr4cqPgykGPpxtU5nKgtlC13aUMRTulJNxtNkEQtCU4MZKVtKMBY09LUAMnIGdFZDI1NTE5IZggGC0YVhiMGPEYGxjCGD8DFBiuGMAYLhhjCRjKGKYY3xhSGBgYnhhnGKEYIQwYPBjeGG0YwRidGIcY8Rjs","clientDataJSON": "eyJjaGFsbGVuZ2UiOiJzTlplbDVPaEl3QTV2UjR3ZFZrd2lHSFI2UUVuTmhZT3FpOTdPSFFyYzJBIiwib3JpZ2luIjoiaHR0cHM6Ly90dWxlYXAtd2ViLnR1bGVhcC1haW8tZGV2LmRvY2tlciIsInR5cGUiOiJ3ZWJhdXRobi5jcmVhdGUifQ"},"type": "public-key"}'
            );
        static::assertInstanceOf(AuthenticatorAttestationResponse::class, $publicKeyCredential->getResponse());
        $source = $this->getAuthenticatorAttestationResponseValidator()
            ->check($publicKeyCredential->getResponse(), $publicKeyCredentialCreationOptions, 'localhost');

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString(
            '{"challenge": "2MSn916xPaaOcp86sSYBVsqYzROi4Y8H7Brl_8D5Drc","allowCredentials": [{"type": "public-key","id": "31ivJEY3jmIoxWuGZ7pZjDuBW5n1PAMeG-e0drfhayCzOsuNaCG3PH43i-OebKT0jqY-bAFCEUh1JCCATSPa9N5QIUwwSlUQO9Pb5X1_yXJnY9q7GYfm3LvR4Yk6-HKj4MpBj6cbVOZyoLZQtd2lDEU7pSTcbTZBELQlODGSlbQ"}]}'
        );
        $publicKeyCredential = $this->getPublicKeyCredentialLoader()
            ->load(
                '{"id": "31ivJEY3jmIoxWuGZ7pZjDuBW5n1PAMeG-e0drfhayCzOsuNaCG3PH43i-OebKT0jqY-bAFCEUh1JCCATSPa9N5QIUwwSlUQO9Pb5X1_yXJnY9q7GYfm3LvR4Yk6-HKj4MpBj6cbVOZyoLZQtd2lDEU7pSTcbTZBELQlODGSlbQ","rawId": "31ivJEY3jmIoxWuGZ7pZjDuBW5n1PAMeG-e0drfhayCzOsuNaCG3PH43i-OebKT0jqY-bAFCEUh1JCCATSPa9N5QIUwwSlUQO9Pb5X1_yXJnY9q7GYfm3LvR4Yk6-HKj4MpBj6cbVOZyoLZQtd2lDEU7pSTcbTZBELQlODGSlbQ","response": {"authenticatorData": "FrAt-8PUzKN-vC9lFmWbEiENueEBirnxOpaQY46m_agFAAAAAg","clientDataJSON": "eyJjaGFsbGVuZ2UiOiIyTVNuOTE2eFBhYU9jcDg2c1NZQlZzcVl6Uk9pNFk4SDdCcmxfOEQ1RHJjIiwib3JpZ2luIjoiaHR0cHM6Ly90dWxlYXAtd2ViLnR1bGVhcC1haW8tZGV2LmRvY2tlciIsInR5cGUiOiJ3ZWJhdXRobi5nZXQifQ","signature": "eK5Yk9G8LjEsaEbK9Qq9Ovcx_Nf9xbRU5EURdMsiqJSQMpSCMHhcOBwfhPxx_zuPfYPxv_mRPgtPrX0vNQ3YAg"},"type": "public-key","clientExtensionResults": {}}'
            );
        static::assertInstanceOf(AuthenticatorAssertionResponse::class, $publicKeyCredential->getResponse());
        $this->getAuthenticatorAssertionResponseValidator()
            ->check(
                $source,
                $publicKeyCredential->getResponse(),
                $publicKeyCredentialRequestOptions,
                'tuleap-web.tuleap-aio-dev.docker',
                '101'
            );
    }
}
