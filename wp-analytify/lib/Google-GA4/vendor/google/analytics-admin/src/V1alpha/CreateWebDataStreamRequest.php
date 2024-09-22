<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/admin/v1alpha/analytics_admin.proto
namespace Google\Analytics\Admin\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Request message for CreateWebDataStream RPC.
 *
 * Generated from protobuf message <code>google.analytics.admin.v1alpha.CreateWebDataStreamRequest</code>
 */
class CreateWebDataStreamRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The web stream to create.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.WebDataStream web_data_stream = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $web_data_stream = null;
    /**
     * Required. The parent resource where this web data stream will be created.
     * Format: properties/123
     *
     * Generated from protobuf field <code>string parent = 2 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Analytics\Admin\V1alpha\WebDataStream $web_data_stream
     *           Required. The web stream to create.
     *     @type string $parent
     *           Required. The parent resource where this web data stream will be created.
     *           Format: properties/123
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Analytics\Admin\V1Alpha\AnalyticsAdmin::initOnce();
        parent::__construct($data);
    }
    /**
     * Required. The web stream to create.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.WebDataStream web_data_stream = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Analytics\Admin\V1alpha\WebDataStream|null
     */
    public function getWebDataStream()
    {
        return $this->web_data_stream;
    }
    public function hasWebDataStream()
    {
        return isset($this->web_data_stream);
    }
    public function clearWebDataStream()
    {
        unset($this->web_data_stream);
    }
    /**
     * Required. The web stream to create.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.WebDataStream web_data_stream = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Analytics\Admin\V1alpha\WebDataStream $var
     * @return $this
     */
    public function setWebDataStream($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1alpha\WebDataStream::class);
        $this->web_data_stream = $var;
        return $this;
    }
    /**
     * Required. The parent resource where this web data stream will be created.
     * Format: properties/123
     *
     * Generated from protobuf field <code>string parent = 2 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * Required. The parent resource where this web data stream will be created.
     * Format: properties/123
     *
     * Generated from protobuf field <code>string parent = 2 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;
        return $this;
    }
}
