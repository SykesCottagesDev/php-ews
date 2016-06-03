<?php

namespace jamesiarmes\PEWS\API\Message;

/**
 * Class representing GetServiceConfigurationType
 *
 *
 * XSD Type: GetServiceConfigurationType
 *
 * @method \jamesiarmes\PEWS\API\Type\EmailAddressType getActingAs()
 * @method GetServiceConfigurationType setActingAs(\jamesiarmes\PEWS\API\Type\EmailAddressType $actingAs)
 * @method GetServiceConfigurationType addRequestedConfiguration(\jamesiarmes\PEWS\API\Enumeration\ServiceConfigurationType $requestedConfiguration)
 * @method \jamesiarmes\PEWS\API\Enumeration\ServiceConfigurationType[] getRequestedConfiguration()
 * @method GetServiceConfigurationType setRequestedConfiguration(array $requestedConfiguration)
 */
class GetServiceConfigurationType extends BaseRequestType
{

    /**
     * @var \jamesiarmes\PEWS\API\Type\EmailAddressType
     */
    protected $actingAs = null;

    /**
     * @var \jamesiarmes\PEWS\API\Enumeration\ServiceConfigurationType[]
     */
    protected $requestedConfiguration = null;
}
