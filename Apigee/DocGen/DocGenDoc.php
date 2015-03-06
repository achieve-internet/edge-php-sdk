<?php

/**
 * @file
 * Reads/Writes to and from the Apigee DocGen modeling API
 *
 * @author bhasselbeck
 * @deprecated
 */

namespace Apigee\DocGen;

use Apigee\Util\APIObject;
use Apigee\Util\OrgConfig;

class DocGenDoc extends APIObject
{

    /**
     * Constructs the proper values for the Apigee DocGen API.
     *
     * @param \Apigee\Util\OrgConfig $config
     */
    public function __construct(OrgConfig $config)
    {
        $this->init($config, '/o/' . rawurlencode($config->orgName) . '/apimodels');
    }

    /**
     * Requests the specific operation, returns HTML.
     *
     * {@inheritDoc}
     */
    public function requestOperation($data, $mid, $name)
    {
        if (empty($name)) {
            $path = $mid . '/revisions/' . $data['revision'] . '/resources/' . $data['resource'] . '/methods/' . $data['operation'];
            $this->get($path);
        }
        else {
            $path = $mid . '/revisions/' . $data['revision'] . '/resources/' . $data['resource'] . '/methods/' . $data['operation'] . '/doc?template=' . $name;
            $this->get($path, 'text/html');
        }
        return $this->responseText;
    }

}