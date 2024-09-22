<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/expr/v1alpha1/conformance_service.proto
namespace Google\Api\Expr\V1alpha1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Request message for the Parse method.
 *
 * Generated from protobuf message <code>google.api.expr.v1alpha1.ParseRequest</code>
 */
class ParseRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Source text in CEL syntax.
     *
     * Generated from protobuf field <code>string cel_source = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $cel_source = '';
    /**
     * Tag for version of CEL syntax, for future use.
     *
     * Generated from protobuf field <code>string syntax_version = 2;</code>
     */
    private $syntax_version = '';
    /**
     * File or resource for source text, used in [SourceInfo][google.api.expr.v1alpha1.SourceInfo].
     *
     * Generated from protobuf field <code>string source_location = 3;</code>
     */
    private $source_location = '';
    /**
     * Prevent macro expansion.  See "Macros" in Language Defiinition.
     *
     * Generated from protobuf field <code>bool disable_macros = 4;</code>
     */
    private $disable_macros = \false;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $cel_source
     *           Required. Source text in CEL syntax.
     *     @type string $syntax_version
     *           Tag for version of CEL syntax, for future use.
     *     @type string $source_location
     *           File or resource for source text, used in [SourceInfo][google.api.expr.v1alpha1.SourceInfo].
     *     @type bool $disable_macros
     *           Prevent macro expansion.  See "Macros" in Language Defiinition.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Api\Expr\V1Alpha1\ConformanceService::initOnce();
        parent::__construct($data);
    }
    /**
     * Required. Source text in CEL syntax.
     *
     * Generated from protobuf field <code>string cel_source = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getCelSource()
    {
        return $this->cel_source;
    }
    /**
     * Required. Source text in CEL syntax.
     *
     * Generated from protobuf field <code>string cel_source = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setCelSource($var)
    {
        GPBUtil::checkString($var, True);
        $this->cel_source = $var;
        return $this;
    }
    /**
     * Tag for version of CEL syntax, for future use.
     *
     * Generated from protobuf field <code>string syntax_version = 2;</code>
     * @return string
     */
    public function getSyntaxVersion()
    {
        return $this->syntax_version;
    }
    /**
     * Tag for version of CEL syntax, for future use.
     *
     * Generated from protobuf field <code>string syntax_version = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setSyntaxVersion($var)
    {
        GPBUtil::checkString($var, True);
        $this->syntax_version = $var;
        return $this;
    }
    /**
     * File or resource for source text, used in [SourceInfo][google.api.expr.v1alpha1.SourceInfo].
     *
     * Generated from protobuf field <code>string source_location = 3;</code>
     * @return string
     */
    public function getSourceLocation()
    {
        return $this->source_location;
    }
    /**
     * File or resource for source text, used in [SourceInfo][google.api.expr.v1alpha1.SourceInfo].
     *
     * Generated from protobuf field <code>string source_location = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setSourceLocation($var)
    {
        GPBUtil::checkString($var, True);
        $this->source_location = $var;
        return $this;
    }
    /**
     * Prevent macro expansion.  See "Macros" in Language Defiinition.
     *
     * Generated from protobuf field <code>bool disable_macros = 4;</code>
     * @return bool
     */
    public function getDisableMacros()
    {
        return $this->disable_macros;
    }
    /**
     * Prevent macro expansion.  See "Macros" in Language Defiinition.
     *
     * Generated from protobuf field <code>bool disable_macros = 4;</code>
     * @param bool $var
     * @return $this
     */
    public function setDisableMacros($var)
    {
        GPBUtil::checkBool($var);
        $this->disable_macros = $var;
        return $this;
    }
}
