<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/expr/v1alpha1/checked.proto
namespace Google\Api\Expr\V1alpha1\Type;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Application defined abstract type.
 *
 * Generated from protobuf message <code>google.api.expr.v1alpha1.Type.AbstractType</code>
 */
class AbstractType extends \Google\Protobuf\Internal\Message
{
    /**
     * The fully qualified name of this abstract type.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * Parameter types for this abstract type.
     *
     * Generated from protobuf field <code>repeated .google.api.expr.v1alpha1.Type parameter_types = 2;</code>
     */
    private $parameter_types;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           The fully qualified name of this abstract type.
     *     @type \Google\Api\Expr\V1alpha1\Type[]|\Google\Protobuf\Internal\RepeatedField $parameter_types
     *           Parameter types for this abstract type.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Api\Expr\V1Alpha1\Checked::initOnce();
        parent::__construct($data);
    }
    /**
     * The fully qualified name of this abstract type.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * The fully qualified name of this abstract type.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;
        return $this;
    }
    /**
     * Parameter types for this abstract type.
     *
     * Generated from protobuf field <code>repeated .google.api.expr.v1alpha1.Type parameter_types = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getParameterTypes()
    {
        return $this->parameter_types;
    }
    /**
     * Parameter types for this abstract type.
     *
     * Generated from protobuf field <code>repeated .google.api.expr.v1alpha1.Type parameter_types = 2;</code>
     * @param \Google\Api\Expr\V1alpha1\Type[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setParameterTypes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Api\Expr\V1alpha1\Type::class);
        $this->parameter_types = $arr;
        return $this;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Api\Expr\V1alpha1\Type\AbstractType::class, \Google\Api\Expr\V1alpha1\Type_AbstractType::class);
