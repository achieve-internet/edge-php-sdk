<?php
namespace Apigee\Mint;

use Apigee\Util\APIObject;
use Apigee\Util\OrgConfig;
use Apigee\Util\CacheFactory;
use Apigee\Exceptions\ResponseException;
use Apigee\Mint\Types\StatusType;
use Apigee\Mint\Types\TaxModelType;
use Apigee\Mint\Types\OrgType;
use Apigee\Mint\Types\Country;
use Apigee\Mint\Types\BillingCycleType;
use Apigee\Mint\Types\BillingType;
use Apigee\Mint\Exceptions\MintApiException;

use Apigee\Mint\DataStructures\SupportedCurrency;
use Apigee\Exceptions\ParameterException;
use Apigee\Exceptions\NotImplementedException;

class Organization extends Base\BaseObject
{
    /**
     * @var array
     */
    private $addresses;

    /**
     * @var bool
     */
    private $approveTrusted;

    /**
     * @var bool
     */
    private $approveUntrusted;

    /**
     * @var string
     */
    private $billingCycle;

    /**
     * @var \Apigee\Mint\Organization
     */
    private $children;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $groupOrganization;

    /**
     * @var bool
     */
    private $hasBroker;

    /**
     * @var bool
     */
    private $hasSelfBilling;

    /**
     * @var bool
     */
    private $hasSeparateInvoiceForProduct;

    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $issueNettingStatement;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $nettingStatementPerCurrency;

    /**
     * @var string
     */
    private $regNo;

    /**
     * @var \Apigee\Mint\Organization
     */
    private $parent;

    /**
     * @var string
     */
    private $orgType;

    /**
     * @var bool
     */
    private $selfBillingAsExchOrg;

    /**
     * @var bool
     */
    private $selfBillingForAllDev;

    /**
     * @var bool
     */
    private $separateInvoiceForFees;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $supportedBillingType;

    /**
     * @var string
     */
    private $taxModel;

    /**
     * @var string
     */
    private $taxNexus;

    /**
     * @var string
     */
    private $taxRegNo;

    /**
     * @var string
     */
    private $timezone;

    public function __construct(OrgConfig $config)
    {
        $base_url = '/mint/organizations';
        $this->init($config, $base_url);
        $this->wrapperTag = 'organization';
        $this->idField = 'name';
        $this->idIsAutogenerated = false;

        $this->initValues();
    }

    public function getList($page_num = null, $page_size = 20)
    {
        throw new NotImplementedException('Organization does not support the getList method.');
    }

    public function listOrganizationIdentifiers()
    {
        $this->setBaseUrl('/organizations');
        $this->get();
        $this->restoreBaseUrl();
        $list = $this->responseObj;
        return $list;
    }

    /**
     * Override parent's load function to optionally pull org name
     * from the APIClient.
     *
     * @param null|string $id
     */
    public function load($id = null)
    {
        if (!isset($id)) {
            $id = $this->name;
        }
        if (!isset($id)) {
            $id = $this->config->orgName;
        }
        $cache_manager = CacheFactory::getCacheManager(null);
        $data = $cache_manager->get('mint_organization:' . $id, null);
        if (!isset($data)) {
            $url = rawurlencode($id);
            $this->get($url);
            $data = $this->responseObj;
            $cache_manager->set('mint_organization:' . $id, $data);
        }
        $this->initValues();
        $this->loadFromRawData($data);
    }

    public function loadFromRawData($data, $reset = false)
    {
        if ($reset) {
            $this->initValues();
        }
        if (isset($data['address'])) {
            foreach ($data['address'] as $address_data) {
                $address = new DataStructures\Address($address_data);
                $this->addresses[] = $address;
            }
        }

        if (isset($data['parent'])) {
            $parent = new Organization($this->config);
            $parent->loadFromRawData($data['parent']);
            $this->parent = $parent;
        }

        $this->taxRegNo = isset($data['taxRegNo']) ? $data['taxRegNo'] : null;
        $this->regNo = isset($data['regNo']) ? $data['regNo'] : null;

        $excluded_properties = array('address', 'regNo', 'taxRegNo', 'parent', 'children');
        foreach (array_keys($data) as $property) {
            if (in_array($property, $excluded_properties)) {
                continue;
            }

            // form the setter method name to invoke setXxxx
            $setter_method = 'set' . ucfirst($property);

            if (method_exists($this, $setter_method)) {
                $this->$setter_method($data[$property]);
            } else {
                self::$logger->notice('No setter method was found for property "' . $property . '"');
            }
        }
    }

    /**
     * Pushes Developers that are missing in Mint from 4G
     *
     * @param $id Organization id, if not specified or null
     *   then this object's organization name is used
     *
     * @return string Text response from 4g request
     */
    public function syncAllFrom4g($id = null)
    {
        if (!isset($id)) {
            $id = $this->name;
        }
        if (!isset($id)) {
            $id = $this->config->orgName;
        }
        if (!isset($id)) {
            throw new ParameterException("Missing organization name");
        }
        $url = rawurlencode($id) . '/sync-organization?childEntities=true';
        $this->get($url);
        return $this->responseText;
    }

    public function __toString()
    {
        $obj = array();
        $obj['address'] = $this->addresses;
        $properties = array_keys(get_object_vars($this));
        $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
        foreach ($properties as $property) {
            if ($property == 'addresses' || in_array($property, $excluded_properties)) {
                continue;
            }
            if (isset($this->$property)) {
                $obj[$property] = $this->$property;
            }
        }
        return json_encode($obj);
    }

    protected function initValues()
    {
        $this->clearAddresses();
        $this->approveTrusted = false;
        $this->approveUntrusted = false;
        $this->billingCycle = 'CALENDAR_MONTH';
        $this->country = 'US';
        $this->currency = 'USD';
        $this->description = '';
        $this->groupOrganization = false;
        $this->hasBroker = false;
        $this->hasSelfBilling = false;
        $this->hasSeparateInvoiceForProduct = false;
        $this->id = '';
        $this->issueNettingStatement = false;
        $this->logoUrl = '';
        $this->name = null;
        $this->nettingStatementPerCurrency = false;
        $this->regNo = '';
        $this->selfBillingAsExchOrg = false;
        $this->selfBillingForAllDev = false;
        $this->separateInvoiceForFees = false;
        $this->status = 'ACTIVE';
        $this->taxModel = 'DISCLOSED';
        $this->taxRegNo = '';
        $this->timezone = 'UTC';
    }

    public function instantiateNew()
    {
        return new Organization($this->config);
    }

    /*
     * accessors (getters/setters)
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    public function addAddress(DataStructures\Address $address)
    {
        $this->addresses[] = $address;
    }

    public function clearAddresses()
    {
        $this->addresses = array();
    }

    // Booleans
    public function getApproveTrusted()
    {
        return $this->approveTrusted;
    }

    public function setApproveTrusted($bool = true)
    {
        $this->approveTrusted = (bool)$bool;
    }

    public function getApproveUntrusted()
    {
        return $this->approveUntrusted;
    }

    public function setApproveUntrusted($bool = true)
    {
        $this->approveUntrusted = (bool)$bool;
    }

    public function getGroupOrganization()
    {
        return $this->groupOrganization;
    }

    public function setGroupOrganization($bool = true)
    {
        $this->groupOrganization = (bool)$bool;
    }

    public function hasBroker()
    {
        return $this->hasBroker;
    }

    public function setHasBroker($bool = true)
    {
        $this->hasBroker = (bool)$bool;
    }

    public function hasSelfBilling()
    {
        return $this->hasSelfBilling;
    }

    public function setHasSelfBilling($bool = true)
    {
        $this->hasSelfBilling = (bool)$bool;
    }

    public function hasSeparateInvoiceForProduct()
    {
        return $this->hasSeparateInvoiceForProduct;
    }

    public function setHasSeparateInvoiceForProduct($bool = true)
    {
        $this->hasSeparateInvoiceForProduct = (bool)$bool;
    }

    public function getIssueNettingStmt()
    {
        return $this->issueNettingStatement;
    }

    public function setIssueNettingStmt($bool = true)
    {
        $this->issueNettingStatement = (bool)$bool;
    }

    public function getNettingStmtPerCurrency()
    {
        return $this->nettingStatementPerCurrency;
    }

    public function setNettingStmtPerCurrency($bool = true)
    {
        $this->nettingStatementPerCurrency = (bool)$bool;
    }

    public function getSelfBillingAsExchOrg()
    {
        return $this->selfBillingAsExchOrg;
    }

    public function setSelfBillingAsExchOrg($bool = true)
    {
        $this->selfBillingAsExchOrg = (bool)$bool;
    }

    public function getSelfBillingForAllDev()
    {
        return $this->selfBillingForAllDev;
    }

    public function setSelfBillingForAllDev($bool = true)
    {
        $this->selfBillingForAllDev = (bool)$bool;
    }

    public function getSeparateInvoiceForFees()
    {
        return $this->separateInvoiceForFees;
    }

    public function setSeparateInvoiceForFees($bool = true)
    {
        $this->separateInvoiceForFees = (bool)$bool;
    }

    public function getBillingCycle()
    {
        return $this->billingCycle;
    }

    public function setBillingCycle($cycle)
    {
        $this->billingCycle = BillingCycleType::get($cycle);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country_code)
    {
        // Only set country if it is valid.
        if (Country::validateCountryCode($country_code)) {
            $this->country = $country_code;
        } elseif ($country_code == 'UK') {
            // Change incorrect United Kingdom 'UK' country code to 'GB'.
            $this->country = 'GB';
        } else {
            APIObject::$logger->error('Invalid country code "' . $country_code . '" passed from Edge MGMT API.');
        }
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($currency)
    {
        // TODO: validate $currency here
        $this->currency = strtoupper($currency);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($desc)
    {
        $this->description = (string)$desc;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = (string)$id;
    }

    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    public function setLogoUrl($url)
    {
        if (empty($url)) {
            $this->logoUrl = null;
        } else {
            if (!$this->validateUri($url)) {
                throw new ParameterException("$url is not a valid logo URL.");
            }
            $this->logoUrl = $url;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = (string)$name;
    }

    public function getOrgType()
    {
        return $this->orgType;
    }

    public function setOrgType($org_type)
    {
        $this->orgType = OrgType::get($org_type);
    }

    /**
     * @return \Apigee\Mint\Organization
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \Apigee\Mint\Organization $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getRegNo()
    {
        return $this->regNo;
    }

    public function setRegNo($num)
    {
        $this->regNo = (string)$num;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = StatusType::get($status);
    }

    public function getSupportedBillingType()
    {
        return $this->supportedBillingType;
    }

    public function setSupportedBillingType($supported_billing_type)
    {
        $this->supportedBillingType = BillingType::get($supported_billing_type);
    }

    public function getTaxModel()
    {
        return $this->taxModel;
    }

    public function setTaxModel($model)
    {
        $this->taxModel = TaxModelType::get($model);
    }

    public function getTaxNexus()
    {
        return $this->taxNexus;
    }

    public function setTaxNexus($tax_nexus)
    {
        $this->taxNexus = $tax_nexus;
    }

    public function getTaxRegNo()
    {
        return $this->taxRegNo;
    }

    public function setTaxRegNo($num)
    {
        $this->taxRegNo = (string)$num;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function setTimezone($tz)
    {
        // TODO: validate $tz. Is this just a string from tzdata?
        $this->timezone = $tz;
    }

    public function listSupportedCurrencies($id = null, $include_children = false)
    {
        if (!isset($id)) {
            $id = $this->config->orgName;
        }
        $options = array(
            'query' => array(
                'include_children' => ($include_children ? 'true' : 'false'),
            )
        );
        $url = rawurlencode($id) . '/supported-currencies';
        $cache_manager = CacheFactory::getCacheManager(null);
        $cache_id = 'supported-currencies:' . $id . '/include_children=' . ($include_children ? 'true' : 'false');
        $data = $cache_manager->get($cache_id, null);

        if ($data === null) {
            $this->get($url, 'application/json; charset=utf-8', array(), $options);
            $data = $this->responseObj;
            $cache_manager->set($cache_id, $data);
        }
        $currencies = array();
        foreach ($data['supportedCurrency'] as $currency_item) {
            $currencies[] = new SupportedCurrency($currency_item);
        }
        return $currencies;
    }

    public function getPrepaidBalanceReport($month, $year, $developer_id, $currency_id)
    {

        try {
            $data = array(
                'showTxDetail' => true,
                'devCriteria' => array(
                    array(
                        'id' => $developer_id,
                        'orgId' => $this->config->orgName
                    )
                ),
                'currCriteria' => array(
                    array(
                        'id' => strtolower($currency_id),
                        'orgId' => $this->config->orgName
                    )
                ),
                'billingMonth' => strtoupper($month),
                'billingYear' => $year
            );

            $url = '/mint/organizations/' . rawurlencode($this->config->orgName) . '/prepaid-balance-reports';
            $content_type = 'application/json; charset=utf-8';
            $accept_type = 'application/octet-stream; charset=utf-8';
            $this->setBaseUrl($url);
            $this->post(null, $data, $content_type, $accept_type);
            $this->restoreBaseUrl();
            $response = $this->responseText;
        } catch (ResponseException $re) {
            if (MintApiException::isMintExceptionCode($re)) {
                throw new MintApiException($re);
            }
            throw $re;
        }
        return $response;
    }

    /**
     *
     * Retrieve Parent organization along with its siblings
     *
     * @param $id Parent organization id
     *
     * @return array of \Apigee\Mint\Organization
     */
    public function getOrganizationFamily($id)
    {
        if (!isset($id)) {
            $id = $this->name;
        }
        if (!isset($id)) {
            $id = $this->config->orgName;
        }
        $cache_manager = CacheFactory::getCacheManager(null);
        $data = $cache_manager->get('mint_organization-family:' . $id, null);
        if (!isset($data)) {
            $url = rawurlencode($id) . '/organization-family';
            $this->get($url);
            $data = $this->responseObj;
            $cache_manager->set('mint_organization-family:' . $id, $data);
        }
        $orgs = array();
        foreach ($data['organization'] as $org) {
            $orgObj = $this->instantiateNew();
            self::$logger = $this->config->logger;
            $orgObj->initValues();
            $orgObj->loadFromRawData($org);
            $orgs[$orgObj->getId()] = $orgObj;
        }
        return $orgs;
    }
}
