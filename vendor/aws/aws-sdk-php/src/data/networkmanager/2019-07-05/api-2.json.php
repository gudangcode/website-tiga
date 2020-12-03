<?php
// This file was auto-generated from sdk-root/src/data/networkmanager/2019-07-05/api-2.json
return [ 'version' => '2.0', 'metadata' => [ 'apiVersion' => '2019-07-05', 'endpointPrefix' => 'networkmanager', 'jsonVersion' => '1.1', 'protocol' => 'rest-json', 'serviceAbbreviation' => 'NetworkManager', 'serviceFullName' => 'AWS Network Manager', 'serviceId' => 'NetworkManager', 'signatureVersion' => 'v4', 'signingName' => 'networkmanager', 'uid' => 'networkmanager-2019-07-05', ], 'operations' => [ 'AssociateCustomerGateway' => [ 'name' => 'AssociateCustomerGateway', 'http' => [ 'method' => 'POST', 'requestUri' => '/global-networks/{globalNetworkId}/customer-gateway-associations', ], 'input' => [ 'shape' => 'AssociateCustomerGatewayRequest', ], 'output' => [ 'shape' => 'AssociateCustomerGatewayResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'AssociateLink' => [ 'name' => 'AssociateLink', 'http' => [ 'method' => 'POST', 'requestUri' => '/global-networks/{globalNetworkId}/link-associations', ], 'input' => [ 'shape' => 'AssociateLinkRequest', ], 'output' => [ 'shape' => 'AssociateLinkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'CreateDevice' => [ 'name' => 'CreateDevice', 'http' => [ 'method' => 'POST', 'requestUri' => '/global-networks/{globalNetworkId}/devices', ], 'input' => [ 'shape' => 'CreateDeviceRequest', ], 'output' => [ 'shape' => 'CreateDeviceResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'CreateGlobalNetwork' => [ 'name' => 'CreateGlobalNetwork', 'http' => [ 'method' => 'POST', 'requestUri' => '/global-networks', ], 'input' => [ 'shape' => 'CreateGlobalNetworkRequest', ], 'output' => [ 'shape' => 'CreateGlobalNetworkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'CreateLink' => [ 'name' => 'CreateLink', 'http' => [ 'method' => 'POST', 'requestUri' => '/global-networks/{globalNetworkId}/links', ], 'input' => [ 'shape' => 'CreateLinkRequest', ], 'output' => [ 'shape' => 'CreateLinkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'CreateSite' => [ 'name' => 'CreateSite', 'http' => [ 'method' => 'POST', 'requestUri' => '/global-networks/{globalNetworkId}/sites', ], 'input' => [ 'shape' => 'CreateSiteRequest', ], 'output' => [ 'shape' => 'CreateSiteResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DeleteDevice' => [ 'name' => 'DeleteDevice', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/global-networks/{globalNetworkId}/devices/{deviceId}', ], 'input' => [ 'shape' => 'DeleteDeviceRequest', ], 'output' => [ 'shape' => 'DeleteDeviceResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DeleteGlobalNetwork' => [ 'name' => 'DeleteGlobalNetwork', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/global-networks/{globalNetworkId}', ], 'input' => [ 'shape' => 'DeleteGlobalNetworkRequest', ], 'output' => [ 'shape' => 'DeleteGlobalNetworkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DeleteLink' => [ 'name' => 'DeleteLink', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/global-networks/{globalNetworkId}/links/{linkId}', ], 'input' => [ 'shape' => 'DeleteLinkRequest', ], 'output' => [ 'shape' => 'DeleteLinkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DeleteSite' => [ 'name' => 'DeleteSite', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/global-networks/{globalNetworkId}/sites/{siteId}', ], 'input' => [ 'shape' => 'DeleteSiteRequest', ], 'output' => [ 'shape' => 'DeleteSiteResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DeregisterTransitGateway' => [ 'name' => 'DeregisterTransitGateway', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/global-networks/{globalNetworkId}/transit-gateway-registrations/{transitGatewayArn}', ], 'input' => [ 'shape' => 'DeregisterTransitGatewayRequest', ], 'output' => [ 'shape' => 'DeregisterTransitGatewayResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DescribeGlobalNetworks' => [ 'name' => 'DescribeGlobalNetworks', 'http' => [ 'method' => 'GET', 'requestUri' => '/global-networks', ], 'input' => [ 'shape' => 'DescribeGlobalNetworksRequest', ], 'output' => [ 'shape' => 'DescribeGlobalNetworksResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DisassociateCustomerGateway' => [ 'name' => 'DisassociateCustomerGateway', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/global-networks/{globalNetworkId}/customer-gateway-associations/{customerGatewayArn}', ], 'input' => [ 'shape' => 'DisassociateCustomerGatewayRequest', ], 'output' => [ 'shape' => 'DisassociateCustomerGatewayResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'DisassociateLink' => [ 'name' => 'DisassociateLink', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/global-networks/{globalNetworkId}/link-associations', ], 'input' => [ 'shape' => 'DisassociateLinkRequest', ], 'output' => [ 'shape' => 'DisassociateLinkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'GetCustomerGatewayAssociations' => [ 'name' => 'GetCustomerGatewayAssociations', 'http' => [ 'method' => 'GET', 'requestUri' => '/global-networks/{globalNetworkId}/customer-gateway-associations', ], 'input' => [ 'shape' => 'GetCustomerGatewayAssociationsRequest', ], 'output' => [ 'shape' => 'GetCustomerGatewayAssociationsResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'GetDevices' => [ 'name' => 'GetDevices', 'http' => [ 'method' => 'GET', 'requestUri' => '/global-networks/{globalNetworkId}/devices', ], 'input' => [ 'shape' => 'GetDevicesRequest', ], 'output' => [ 'shape' => 'GetDevicesResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'GetLinkAssociations' => [ 'name' => 'GetLinkAssociations', 'http' => [ 'method' => 'GET', 'requestUri' => '/global-networks/{globalNetworkId}/link-associations', ], 'input' => [ 'shape' => 'GetLinkAssociationsRequest', ], 'output' => [ 'shape' => 'GetLinkAssociationsResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'GetLinks' => [ 'name' => 'GetLinks', 'http' => [ 'method' => 'GET', 'requestUri' => '/global-networks/{globalNetworkId}/links', ], 'input' => [ 'shape' => 'GetLinksRequest', ], 'output' => [ 'shape' => 'GetLinksResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'GetSites' => [ 'name' => 'GetSites', 'http' => [ 'method' => 'GET', 'requestUri' => '/global-networks/{globalNetworkId}/sites', ], 'input' => [ 'shape' => 'GetSitesRequest', ], 'output' => [ 'shape' => 'GetSitesResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'GetTransitGatewayRegistrations' => [ 'name' => 'GetTransitGatewayRegistrations', 'http' => [ 'method' => 'GET', 'requestUri' => '/global-networks/{globalNetworkId}/transit-gateway-registrations', ], 'input' => [ 'shape' => 'GetTransitGatewayRegistrationsRequest', ], 'output' => [ 'shape' => 'GetTransitGatewayRegistrationsResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'ListTagsForResource' => [ 'name' => 'ListTagsForResource', 'http' => [ 'method' => 'GET', 'requestUri' => '/tags/{resourceArn}', ], 'input' => [ 'shape' => 'ListTagsForResourceRequest', ], 'output' => [ 'shape' => 'ListTagsForResourceResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'RegisterTransitGateway' => [ 'name' => 'RegisterTransitGateway', 'http' => [ 'method' => 'POST', 'requestUri' => '/global-networks/{globalNetworkId}/transit-gateway-registrations', ], 'input' => [ 'shape' => 'RegisterTransitGatewayRequest', ], 'output' => [ 'shape' => 'RegisterTransitGatewayResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'TagResource' => [ 'name' => 'TagResource', 'http' => [ 'method' => 'POST', 'requestUri' => '/tags/{resourceArn}', ], 'input' => [ 'shape' => 'TagResourceRequest', ], 'output' => [ 'shape' => 'TagResourceResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'UntagResource' => [ 'name' => 'UntagResource', 'http' => [ 'method' => 'DELETE', 'requestUri' => '/tags/{resourceArn}', ], 'input' => [ 'shape' => 'UntagResourceRequest', ], 'output' => [ 'shape' => 'UntagResourceResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'UpdateDevice' => [ 'name' => 'UpdateDevice', 'http' => [ 'method' => 'PATCH', 'requestUri' => '/global-networks/{globalNetworkId}/devices/{deviceId}', ], 'input' => [ 'shape' => 'UpdateDeviceRequest', ], 'output' => [ 'shape' => 'UpdateDeviceResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'UpdateGlobalNetwork' => [ 'name' => 'UpdateGlobalNetwork', 'http' => [ 'method' => 'PATCH', 'requestUri' => '/global-networks/{globalNetworkId}', ], 'input' => [ 'shape' => 'UpdateGlobalNetworkRequest', ], 'output' => [ 'shape' => 'UpdateGlobalNetworkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'UpdateLink' => [ 'name' => 'UpdateLink', 'http' => [ 'method' => 'PATCH', 'requestUri' => '/global-networks/{globalNetworkId}/links/{linkId}', ], 'input' => [ 'shape' => 'UpdateLinkRequest', ], 'output' => [ 'shape' => 'UpdateLinkResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'ServiceQuotaExceededException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], 'UpdateSite' => [ 'name' => 'UpdateSite', 'http' => [ 'method' => 'PATCH', 'requestUri' => '/global-networks/{globalNetworkId}/sites/{siteId}', ], 'input' => [ 'shape' => 'UpdateSiteRequest', ], 'output' => [ 'shape' => 'UpdateSiteResponse', ], 'errors' => [ [ 'shape' => 'ValidationException', ], [ 'shape' => 'AccessDeniedException', ], [ 'shape' => 'ResourceNotFoundException', ], [ 'shape' => 'ConflictException', ], [ 'shape' => 'ThrottlingException', ], [ 'shape' => 'InternalServerException', ], ], ], ], 'shapes' => [ 'AccessDeniedException' => [ 'type' => 'structure', 'required' => [ 'Message', ], 'members' => [ 'Message' => [ 'shape' => 'String', ], ], 'error' => [ 'httpStatusCode' => 403, ], 'exception' => true, ], 'AssociateCustomerGatewayRequest' => [ 'type' => 'structure', 'required' => [ 'CustomerGatewayArn', 'GlobalNetworkId', 'DeviceId', ], 'members' => [ 'CustomerGatewayArn' => [ 'shape' => 'String', ], 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'DeviceId' => [ 'shape' => 'String', ], 'LinkId' => [ 'shape' => 'String', ], ], ], 'AssociateCustomerGatewayResponse' => [ 'type' => 'structure', 'members' => [ 'CustomerGatewayAssociation' => [ 'shape' => 'CustomerGatewayAssociation', ], ], ], 'AssociateLinkRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'DeviceId', 'LinkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'DeviceId' => [ 'shape' => 'String', ], 'LinkId' => [ 'shape' => 'String', ], ], ], 'AssociateLinkResponse' => [ 'type' => 'structure', 'members' => [ 'LinkAssociation' => [ 'shape' => 'LinkAssociation', ], ], ], 'Bandwidth' => [ 'type' => 'structure', 'members' => [ 'UploadSpeed' => [ 'shape' => 'Integer', ], 'DownloadSpeed' => [ 'shape' => 'Integer', ], ], ], 'ConflictException' => [ 'type' => 'structure', 'required' => [ 'Message', 'ResourceId', 'ResourceType', ], 'members' => [ 'Message' => [ 'shape' => 'String', ], 'ResourceId' => [ 'shape' => 'String', ], 'ResourceType' => [ 'shape' => 'String', ], ], 'error' => [ 'httpStatusCode' => 409, ], 'exception' => true, ], 'CreateDeviceRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'Description' => [ 'shape' => 'String', ], 'Type' => [ 'shape' => 'String', ], 'Vendor' => [ 'shape' => 'String', ], 'Model' => [ 'shape' => 'String', ], 'SerialNumber' => [ 'shape' => 'String', ], 'Location' => [ 'shape' => 'Location', ], 'SiteId' => [ 'shape' => 'String', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'CreateDeviceResponse' => [ 'type' => 'structure', 'members' => [ 'Device' => [ 'shape' => 'Device', ], ], ], 'CreateGlobalNetworkRequest' => [ 'type' => 'structure', 'members' => [ 'Description' => [ 'shape' => 'String', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'CreateGlobalNetworkResponse' => [ 'type' => 'structure', 'members' => [ 'GlobalNetwork' => [ 'shape' => 'GlobalNetwork', ], ], ], 'CreateLinkRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'Bandwidth', 'SiteId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'Description' => [ 'shape' => 'String', ], 'Type' => [ 'shape' => 'String', ], 'Bandwidth' => [ 'shape' => 'Bandwidth', ], 'Provider' => [ 'shape' => 'String', ], 'SiteId' => [ 'shape' => 'String', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'CreateLinkResponse' => [ 'type' => 'structure', 'members' => [ 'Link' => [ 'shape' => 'Link', ], ], ], 'CreateSiteRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'Description' => [ 'shape' => 'String', ], 'Location' => [ 'shape' => 'Location', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'CreateSiteResponse' => [ 'type' => 'structure', 'members' => [ 'Site' => [ 'shape' => 'Site', ], ], ], 'CustomerGatewayAssociation' => [ 'type' => 'structure', 'members' => [ 'CustomerGatewayArn' => [ 'shape' => 'String', ], 'GlobalNetworkId' => [ 'shape' => 'String', ], 'DeviceId' => [ 'shape' => 'String', ], 'LinkId' => [ 'shape' => 'String', ], 'State' => [ 'shape' => 'CustomerGatewayAssociationState', ], ], ], 'CustomerGatewayAssociationList' => [ 'type' => 'list', 'member' => [ 'shape' => 'CustomerGatewayAssociation', ], ], 'CustomerGatewayAssociationState' => [ 'type' => 'string', 'enum' => [ 'PENDING', 'AVAILABLE', 'DELETING', 'DELETED', ], ], 'DateTime' => [ 'type' => 'timestamp', ], 'DeleteDeviceRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'DeviceId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'DeviceId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'deviceId', ], ], ], 'DeleteDeviceResponse' => [ 'type' => 'structure', 'members' => [ 'Device' => [ 'shape' => 'Device', ], ], ], 'DeleteGlobalNetworkRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], ], ], 'DeleteGlobalNetworkResponse' => [ 'type' => 'structure', 'members' => [ 'GlobalNetwork' => [ 'shape' => 'GlobalNetwork', ], ], ], 'DeleteLinkRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'LinkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'LinkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'linkId', ], ], ], 'DeleteLinkResponse' => [ 'type' => 'structure', 'members' => [ 'Link' => [ 'shape' => 'Link', ], ], ], 'DeleteSiteRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'SiteId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'SiteId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'siteId', ], ], ], 'DeleteSiteResponse' => [ 'type' => 'structure', 'members' => [ 'Site' => [ 'shape' => 'Site', ], ], ], 'DeregisterTransitGatewayRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'TransitGatewayArn', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'TransitGatewayArn' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'transitGatewayArn', ], ], ], 'DeregisterTransitGatewayResponse' => [ 'type' => 'structure', 'members' => [ 'TransitGatewayRegistration' => [ 'shape' => 'TransitGatewayRegistration', ], ], ], 'DescribeGlobalNetworksRequest' => [ 'type' => 'structure', 'members' => [ 'GlobalNetworkIds' => [ 'shape' => 'StringList', 'location' => 'querystring', 'locationName' => 'globalNetworkIds', ], 'MaxResults' => [ 'shape' => 'MaxResults', 'location' => 'querystring', 'locationName' => 'maxResults', ], 'NextToken' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'nextToken', ], ], ], 'DescribeGlobalNetworksResponse' => [ 'type' => 'structure', 'members' => [ 'GlobalNetworks' => [ 'shape' => 'GlobalNetworkList', ], 'NextToken' => [ 'shape' => 'String', ], ], ], 'Device' => [ 'type' => 'structure', 'members' => [ 'DeviceId' => [ 'shape' => 'String', ], 'DeviceArn' => [ 'shape' => 'String', ], 'GlobalNetworkId' => [ 'shape' => 'String', ], 'Description' => [ 'shape' => 'String', ], 'Type' => [ 'shape' => 'String', ], 'Vendor' => [ 'shape' => 'String', ], 'Model' => [ 'shape' => 'String', ], 'SerialNumber' => [ 'shape' => 'String', ], 'Location' => [ 'shape' => 'Location', ], 'SiteId' => [ 'shape' => 'String', ], 'CreatedAt' => [ 'shape' => 'DateTime', ], 'State' => [ 'shape' => 'DeviceState', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'DeviceList' => [ 'type' => 'list', 'member' => [ 'shape' => 'Device', ], ], 'DeviceState' => [ 'type' => 'string', 'enum' => [ 'PENDING', 'AVAILABLE', 'DELETING', 'UPDATING', ], ], 'DisassociateCustomerGatewayRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'CustomerGatewayArn', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'CustomerGatewayArn' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'customerGatewayArn', ], ], ], 'DisassociateCustomerGatewayResponse' => [ 'type' => 'structure', 'members' => [ 'CustomerGatewayAssociation' => [ 'shape' => 'CustomerGatewayAssociation', ], ], ], 'DisassociateLinkRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'DeviceId', 'LinkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'DeviceId' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'deviceId', ], 'LinkId' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'linkId', ], ], ], 'DisassociateLinkResponse' => [ 'type' => 'structure', 'members' => [ 'LinkAssociation' => [ 'shape' => 'LinkAssociation', ], ], ], 'GetCustomerGatewayAssociationsRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'CustomerGatewayArns' => [ 'shape' => 'StringList', 'location' => 'querystring', 'locationName' => 'customerGatewayArns', ], 'MaxResults' => [ 'shape' => 'MaxResults', 'location' => 'querystring', 'locationName' => 'maxResults', ], 'NextToken' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'nextToken', ], ], ], 'GetCustomerGatewayAssociationsResponse' => [ 'type' => 'structure', 'members' => [ 'CustomerGatewayAssociations' => [ 'shape' => 'CustomerGatewayAssociationList', ], 'NextToken' => [ 'shape' => 'String', ], ], ], 'GetDevicesRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'DeviceIds' => [ 'shape' => 'StringList', 'location' => 'querystring', 'locationName' => 'deviceIds', ], 'SiteId' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'siteId', ], 'MaxResults' => [ 'shape' => 'MaxResults', 'location' => 'querystring', 'locationName' => 'maxResults', ], 'NextToken' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'nextToken', ], ], ], 'GetDevicesResponse' => [ 'type' => 'structure', 'members' => [ 'Devices' => [ 'shape' => 'DeviceList', ], 'NextToken' => [ 'shape' => 'String', ], ], ], 'GetLinkAssociationsRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'DeviceId' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'deviceId', ], 'LinkId' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'linkId', ], 'MaxResults' => [ 'shape' => 'MaxResults', 'location' => 'querystring', 'locationName' => 'maxResults', ], 'NextToken' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'nextToken', ], ], ], 'GetLinkAssociationsResponse' => [ 'type' => 'structure', 'members' => [ 'LinkAssociations' => [ 'shape' => 'LinkAssociationList', ], 'NextToken' => [ 'shape' => 'String', ], ], ], 'GetLinksRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'LinkIds' => [ 'shape' => 'StringList', 'location' => 'querystring', 'locationName' => 'linkIds', ], 'SiteId' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'siteId', ], 'Type' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'type', ], 'Provider' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'provider', ], 'MaxResults' => [ 'shape' => 'MaxResults', 'location' => 'querystring', 'locationName' => 'maxResults', ], 'NextToken' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'nextToken', ], ], ], 'GetLinksResponse' => [ 'type' => 'structure', 'members' => [ 'Links' => [ 'shape' => 'LinkList', ], 'NextToken' => [ 'shape' => 'String', ], ], ], 'GetSitesRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'SiteIds' => [ 'shape' => 'StringList', 'location' => 'querystring', 'locationName' => 'siteIds', ], 'MaxResults' => [ 'shape' => 'MaxResults', 'location' => 'querystring', 'locationName' => 'maxResults', ], 'NextToken' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'nextToken', ], ], ], 'GetSitesResponse' => [ 'type' => 'structure', 'members' => [ 'Sites' => [ 'shape' => 'SiteList', ], 'NextToken' => [ 'shape' => 'String', ], ], ], 'GetTransitGatewayRegistrationsRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'TransitGatewayArns' => [ 'shape' => 'StringList', 'location' => 'querystring', 'locationName' => 'transitGatewayArns', ], 'MaxResults' => [ 'shape' => 'MaxResults', 'location' => 'querystring', 'locationName' => 'maxResults', ], 'NextToken' => [ 'shape' => 'String', 'location' => 'querystring', 'locationName' => 'nextToken', ], ], ], 'GetTransitGatewayRegistrationsResponse' => [ 'type' => 'structure', 'members' => [ 'TransitGatewayRegistrations' => [ 'shape' => 'TransitGatewayRegistrationList', ], 'NextToken' => [ 'shape' => 'String', ], ], ], 'GlobalNetwork' => [ 'type' => 'structure', 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', ], 'GlobalNetworkArn' => [ 'shape' => 'String', ], 'Description' => [ 'shape' => 'String', ], 'CreatedAt' => [ 'shape' => 'DateTime', ], 'State' => [ 'shape' => 'GlobalNetworkState', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'GlobalNetworkList' => [ 'type' => 'list', 'member' => [ 'shape' => 'GlobalNetwork', ], ], 'GlobalNetworkState' => [ 'type' => 'string', 'enum' => [ 'PENDING', 'AVAILABLE', 'DELETING', 'UPDATING', ], ], 'Integer' => [ 'type' => 'integer', ], 'InternalServerException' => [ 'type' => 'structure', 'required' => [ 'Message', ], 'members' => [ 'Message' => [ 'shape' => 'String', ], 'RetryAfterSeconds' => [ 'shape' => 'RetryAfterSeconds', 'location' => 'header', 'locationName' => 'Retry-After', ], ], 'error' => [ 'httpStatusCode' => 500, ], 'exception' => true, 'fault' => true, ], 'Link' => [ 'type' => 'structure', 'members' => [ 'LinkId' => [ 'shape' => 'String', ], 'LinkArn' => [ 'shape' => 'String', ], 'GlobalNetworkId' => [ 'shape' => 'String', ], 'SiteId' => [ 'shape' => 'String', ], 'Description' => [ 'shape' => 'String', ], 'Type' => [ 'shape' => 'String', ], 'Bandwidth' => [ 'shape' => 'Bandwidth', ], 'Provider' => [ 'shape' => 'String', ], 'CreatedAt' => [ 'shape' => 'DateTime', ], 'State' => [ 'shape' => 'LinkState', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'LinkAssociation' => [ 'type' => 'structure', 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', ], 'DeviceId' => [ 'shape' => 'String', ], 'LinkId' => [ 'shape' => 'String', ], 'LinkAssociationState' => [ 'shape' => 'LinkAssociationState', ], ], ], 'LinkAssociationList' => [ 'type' => 'list', 'member' => [ 'shape' => 'LinkAssociation', ], ], 'LinkAssociationState' => [ 'type' => 'string', 'enum' => [ 'PENDING', 'AVAILABLE', 'DELETING', 'DELETED', ], ], 'LinkList' => [ 'type' => 'list', 'member' => [ 'shape' => 'Link', ], ], 'LinkState' => [ 'type' => 'string', 'enum' => [ 'PENDING', 'AVAILABLE', 'DELETING', 'UPDATING', ], ], 'ListTagsForResourceRequest' => [ 'type' => 'structure', 'required' => [ 'ResourceArn', ], 'members' => [ 'ResourceArn' => [ 'shape' => 'ResourceARN', 'location' => 'uri', 'locationName' => 'resourceArn', ], ], ], 'ListTagsForResourceResponse' => [ 'type' => 'structure', 'members' => [ 'TagList' => [ 'shape' => 'TagList', ], ], ], 'Location' => [ 'type' => 'structure', 'members' => [ 'Address' => [ 'shape' => 'String', ], 'Latitude' => [ 'shape' => 'String', ], 'Longitude' => [ 'shape' => 'String', ], ], ], 'MaxResults' => [ 'type' => 'integer', 'max' => 500, 'min' => 1, ], 'RegisterTransitGatewayRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'TransitGatewayArn', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'TransitGatewayArn' => [ 'shape' => 'String', ], ], ], 'RegisterTransitGatewayResponse' => [ 'type' => 'structure', 'members' => [ 'TransitGatewayRegistration' => [ 'shape' => 'TransitGatewayRegistration', ], ], ], 'ResourceARN' => [ 'type' => 'string', ], 'ResourceNotFoundException' => [ 'type' => 'structure', 'required' => [ 'Message', 'ResourceId', 'ResourceType', ], 'members' => [ 'Message' => [ 'shape' => 'String', ], 'ResourceId' => [ 'shape' => 'String', ], 'ResourceType' => [ 'shape' => 'String', ], ], 'error' => [ 'httpStatusCode' => 404, ], 'exception' => true, ], 'RetryAfterSeconds' => [ 'type' => 'integer', ], 'ServiceQuotaExceededException' => [ 'type' => 'structure', 'required' => [ 'Message', 'LimitCode', 'ServiceCode', ], 'members' => [ 'Message' => [ 'shape' => 'String', ], 'ResourceId' => [ 'shape' => 'String', ], 'ResourceType' => [ 'shape' => 'String', ], 'LimitCode' => [ 'shape' => 'String', ], 'ServiceCode' => [ 'shape' => 'String', ], ], 'error' => [ 'httpStatusCode' => 402, ], 'exception' => true, ], 'Site' => [ 'type' => 'structure', 'members' => [ 'SiteId' => [ 'shape' => 'String', ], 'SiteArn' => [ 'shape' => 'String', ], 'GlobalNetworkId' => [ 'shape' => 'String', ], 'Description' => [ 'shape' => 'String', ], 'Location' => [ 'shape' => 'Location', ], 'CreatedAt' => [ 'shape' => 'DateTime', ], 'State' => [ 'shape' => 'SiteState', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'SiteList' => [ 'type' => 'list', 'member' => [ 'shape' => 'Site', ], ], 'SiteState' => [ 'type' => 'string', 'enum' => [ 'PENDING', 'AVAILABLE', 'DELETING', 'UPDATING', ], ], 'String' => [ 'type' => 'string', ], 'StringList' => [ 'type' => 'list', 'member' => [ 'shape' => 'String', ], ], 'Tag' => [ 'type' => 'structure', 'members' => [ 'Key' => [ 'shape' => 'TagKey', ], 'Value' => [ 'shape' => 'TagValue', ], ], ], 'TagKey' => [ 'type' => 'string', ], 'TagKeyList' => [ 'type' => 'list', 'member' => [ 'shape' => 'TagKey', ], ], 'TagList' => [ 'type' => 'list', 'member' => [ 'shape' => 'Tag', ], ], 'TagResourceRequest' => [ 'type' => 'structure', 'required' => [ 'ResourceArn', 'Tags', ], 'members' => [ 'ResourceArn' => [ 'shape' => 'ResourceARN', 'location' => 'uri', 'locationName' => 'resourceArn', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'TagResourceResponse' => [ 'type' => 'structure', 'members' => [], ], 'TagValue' => [ 'type' => 'string', ], 'ThrottlingException' => [ 'type' => 'structure', 'required' => [ 'Message', ], 'members' => [ 'Message' => [ 'shape' => 'String', ], 'RetryAfterSeconds' => [ 'shape' => 'RetryAfterSeconds', 'location' => 'header', 'locationName' => 'Retry-After', ], ], 'error' => [ 'httpStatusCode' => 429, ], 'exception' => true, ], 'TransitGatewayRegistration' => [ 'type' => 'structure', 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', ], 'TransitGatewayArn' => [ 'shape' => 'String', ], 'State' => [ 'shape' => 'TransitGatewayRegistrationStateReason', ], ], ], 'TransitGatewayRegistrationList' => [ 'type' => 'list', 'member' => [ 'shape' => 'TransitGatewayRegistration', ], ], 'TransitGatewayRegistrationState' => [ 'type' => 'string', 'enum' => [ 'PENDING', 'AVAILABLE', 'DELETING', 'DELETED', 'FAILED', ], ], 'TransitGatewayRegistrationStateReason' => [ 'type' => 'structure', 'members' => [ 'Code' => [ 'shape' => 'TransitGatewayRegistrationState', ], 'Message' => [ 'shape' => 'String', ], ], ], 'UntagResourceRequest' => [ 'type' => 'structure', 'required' => [ 'ResourceArn', 'TagKeys', ], 'members' => [ 'ResourceArn' => [ 'shape' => 'ResourceARN', 'location' => 'uri', 'locationName' => 'resourceArn', ], 'TagKeys' => [ 'shape' => 'TagKeyList', 'location' => 'querystring', 'locationName' => 'tagKeys', ], ], ], 'UntagResourceResponse' => [ 'type' => 'structure', 'members' => [], ], 'UpdateDeviceRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'DeviceId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'DeviceId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'deviceId', ], 'Description' => [ 'shape' => 'String', ], 'Type' => [ 'shape' => 'String', ], 'Vendor' => [ 'shape' => 'String', ], 'Model' => [ 'shape' => 'String', ], 'SerialNumber' => [ 'shape' => 'String', ], 'Location' => [ 'shape' => 'Location', ], 'SiteId' => [ 'shape' => 'String', ], ], ], 'UpdateDeviceResponse' => [ 'type' => 'structure', 'members' => [ 'Device' => [ 'shape' => 'Device', ], ], ], 'UpdateGlobalNetworkRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'Description' => [ 'shape' => 'String', ], ], ], 'UpdateGlobalNetworkResponse' => [ 'type' => 'structure', 'members' => [ 'GlobalNetwork' => [ 'shape' => 'GlobalNetwork', ], ], ], 'UpdateLinkRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'LinkId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'LinkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'linkId', ], 'Description' => [ 'shape' => 'String', ], 'Type' => [ 'shape' => 'String', ], 'Bandwidth' => [ 'shape' => 'Bandwidth', ], 'Provider' => [ 'shape' => 'String', ], ], ], 'UpdateLinkResponse' => [ 'type' => 'structure', 'members' => [ 'Link' => [ 'shape' => 'Link', ], ], ], 'UpdateSiteRequest' => [ 'type' => 'structure', 'required' => [ 'GlobalNetworkId', 'SiteId', ], 'members' => [ 'GlobalNetworkId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'globalNetworkId', ], 'SiteId' => [ 'shape' => 'String', 'location' => 'uri', 'locationName' => 'siteId', ], 'Description' => [ 'shape' => 'String', ], 'Location' => [ 'shape' => 'Location', ], ], ], 'UpdateSiteResponse' => [ 'type' => 'structure', 'members' => [ 'Site' => [ 'shape' => 'Site', ], ], ], 'ValidationException' => [ 'type' => 'structure', 'required' => [ 'Message', ], 'members' => [ 'Message' => [ 'shape' => 'String', ], 'Reason' => [ 'shape' => 'ValidationExceptionReason', ], 'Fields' => [ 'shape' => 'ValidationExceptionFieldList', ], ], 'error' => [ 'httpStatusCode' => 400, ], 'exception' => true, ], 'ValidationExceptionField' => [ 'type' => 'structure', 'required' => [ 'Name', 'Message', ], 'members' => [ 'Name' => [ 'shape' => 'String', ], 'Message' => [ 'shape' => 'String', ], ], ], 'ValidationExceptionFieldList' => [ 'type' => 'list', 'member' => [ 'shape' => 'ValidationExceptionField', ], ], 'ValidationExceptionReason' => [ 'type' => 'string', 'enum' => [ 'UnknownOperation', 'CannotParse', 'FieldValidationFailed', 'Other', ], ], ],];
