<?php
$server = new SoapServer('mwt.wsdl');
$server->setPersistence(SOAP_PERSISTENCE_SESSION);
$server->handle();
?>