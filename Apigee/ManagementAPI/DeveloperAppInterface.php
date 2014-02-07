<?php

namespace Apigee\ManagementAPI;

interface DeveloperAppInterface
{


    /**
     * Populates this object with information retrieved from the Management API.
     *
     * If $name is not passed, $this->name is used.
     *
     * @param null|string $name
     */
    public function load($name = NULL);

    /**
     * Checks to see if an app with the given name exists for this developer.
     *
     * If $name is not passed, $this->name is used.
     *
     * @param null|string $name
     * @return bool
     */
    public function validate($name = NULL);

    /**
     * Write this app's data to the Management API, preserving client key/secret.
     *
     * The function attempts to determine if this should be an insert or an
     * update automagically. However, when $force_update is set to TRUE, this
     * determination is short-circuited and an update is assumed.
     *
     * @param bool $force_update
     */
    public function save($force_update = FALSE);

    /**
     * Approves or revokes a client key for an app, and optionally also for all
     * API Products associated with that app.
     *
     * @param mixed $status
     *        May be TRUE, FALSE, 0, 1, 'approve' or 'revoke'.
     * @param bool $also_set_apiproduct
     * @throws \Apigee\Exceptions\ParameterException
     */
    public function setKeyStatus($status, $also_set_apiproduct = TRUE);

    /**
     * Deletes a developer app from the Management API.
     *
     * If $name is not passed, $this->name is used.
     *
     * @param null|string $name
     */
    public function delete($name = NULL);

    /**
     * Returns names of all apps belonging to this developer.
     *
     * @return array
     */
    public function getList();

    /**
     * Returns array of all DeveloperApp objects belonging to this developer.
     *
     * @param string|NULL $developer_mail
     * @return array
     */
    public function getListDetail($developer_mail = NULL);

    /**
     * Creates a key/secret pair for this app against its component APIProducts.
     *
     * @todo Find out if we need to individually set the key on each APIProduct.
     *
     * @param string $consumer_key
     * @param string $consumer_secret
     * @throws \Apigee\Exceptions\ParameterException
     */
    public function createKey($consumer_key, $consumer_secret);

    /**
     * Deletes a given key from a developer app.
     *
     * @param string $consumer_key
     */
    public function deleteKey($consumer_key);

    /**
     * Lists all apps within the org. Each member of the returned array is a
     * fully-populated DeveloperApp object.
     *
     * @return array
     */
    public function listAllOrgApps();

    /**
     * Restores this object to its pristine state.
     */
    public function blankValues();

     /**
      * Returns the array of API products with which the app is associated.
      * @return array
      */
    public function getApiProducts();

     /**
      * Sets the array of API products with which the app is associated.
      * @param array 
      */
    public function setApiProducts($products);

     /**
      * Returns the array of name/value pairs used to extend the default app 
      * profile with which the app is associated.
      * @return array
      */
    public function getAttributes();

     /**
      * Returns TRUE if the app attributes array contains $attr.
      * @param string $attr
      * @return bool
      */
    public function hasAttribute($attr);

     /**
      * Returns the value of the specified app attribute, or NULL if the 
      * attribute does not exist.
      * @param string $attr
      */
    public function getAttribute($attr);

     /**
      * Sets the value of the app attrbute.
      * @param string $attr
      * @param 
      */
    public function setAttribute($attr, $value);

     /**
      * Sets the app name.
      * @param string
      */
    public function setName($name);

     /**
      * Returns the app name.
      * @return string
      */
    public function getName();

     /**
      * Sets the callback URL.
      * @param string
      */
    public function setCallbackUrl($url);

     /**
      * Returns the app name.
      * @return string
      */
    public function getCallbackUrl();

     /**
      * Sets the app description.
      * @param string
      */
    public function setDescription($descr);

     /**
      * Returns the app description.
      * @return string
      */
    public function getDescription();

     /**
      * Sets the app access type as 'read', 'write', or 'both'.
      * @param string
      */
    public function setAccessType($type);

     /**
      * Returns the app access type as 'read', 'write', or 'both'.
      * @return string
      */
    public function getAccessType();

     /**
      * Returns the app status of the app: 'approved' or 'unapproved'.
      * @return string
      */
    public function getStatus();

     /**
      * Returns the developer_id attribute of the developer who owns this app.
      * @return string
      */
    public function getDeveloperId();

     /**
      * Returns the email address of thedeveloper who owns this app.
      * @return string
      */
    public function getDeveloperMail();

     /**
      * Returns the status of the consumer key for each API Product: 
      * 'approved' or 'pending'. 
      * Each member of this array is itself an associative array, with keys 
      * of 'apiproduct' and 'status'.
      * @return array
      */
    public function getCredentialApiProducts();

     /**
      * Returns the value of the consumer key for the app.
      * @return string
      */
    public function getConsumerKey();

     /**
      * Returns the value of the consumer secret for the app.
      * @return string
      */
    public function getConsumerSecret();

     /**
      * Returns the scope of the active credentials.
      * @return string
      */
    public function getCredentialScopes();

     /**
      * Returns the status of the consumer key for the app: 'approved' or 'pending'.
      * @return string
      */
    public function getCredentialStatus();

     /**
      * Returns the Unix time when the app was created.
      * @return integer
      */
    public function getCreatedAt();

     /**
      * Returns the username of the developer who created the app.
      * @return string
      */
    public function getCreatedBy();

     /**
      * Returns the Unix time when the app was last modified.
      * @return integer
      */
    public function getModifiedAt();

     /**
      * Returns the username of the developer who last modified the app.
      * @return string
      */
    public function getModifiedBy();

     /**
      * Returns the value of an attribute used to extend the 
      * default credential's profile.
      * @param string
      * @return 
      */
    public function getCredentialAttribute($attr_name);

     /**
      * Sets a name/value pair used to extend the default credential's profile.
      * @param string
      * @param 
      */
    public function setCredentialAttribute($name, $value);

     /**
      * Returns the array of name/value pairs used to extend the default 
      * credential's profile.
      * @return string
      */
    public function getCredentialAttributes();

     /**
      * Clears the array of name/value pairs used to extend the default 
      * credential's profile.
      */
    public function clearCredentialAttributes();

     /**
      * Returns the GUID of this app.
      * @return string
      */
    public function getAppId();

     /**
      * Returns the name of the app family containing the app.
      * @return string
      */
    public function getAppFamily();

     /**
      * Sets the name of the app family containing the app.
      * @param string
      */
    public function setAppFamily($family);

     /**
      * Returns the scope of the app.
      * @return string
      */
    public function getScopes();

     /**
      * Returns TRUE if the $credentialApiproducts, $consumerKey, $consumerSecret, 
      * $credentialScopes, and $credentialStatus properties area all set 
      * to nonNULL values. 
      * Otherwise, returns FALSE.
      */
    public function hasCredentialInfo();
}