<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/protobuf/descriptor.proto
namespace Google\Protobuf\Internal;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\GPBWire;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\InputStream;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Describes a complete .proto file.
 *
 * Generated from protobuf message <code>google.protobuf.FileDescriptorProto</code>
 */
class FileDescriptorProto extends \Google\Protobuf\Internal\Message
{
    /**
     * file name, relative to root of source tree
     *
     * Generated from protobuf field <code>optional string name = 1;</code>
     */
    protected $name = null;
    /**
     * e.g. "foo", "foo.bar", etc.
     *
     * Generated from protobuf field <code>optional string package = 2;</code>
     */
    protected $package = null;
    /**
     * Names of files imported by this file.
     *
     * Generated from protobuf field <code>repeated string dependency = 3;</code>
     */
    private $dependency;
    /**
     * Indexes of the public imported files in the dependency list above.
     *
     * Generated from protobuf field <code>repeated int32 public_dependency = 10;</code>
     */
    private $public_dependency;
    /**
     * Indexes of the weak imported files in the dependency list.
     * For Google-internal migration only. Do not use.
     *
     * Generated from protobuf field <code>repeated int32 weak_dependency = 11;</code>
     */
    private $weak_dependency;
    /**
     * All top-level definitions in this file.
     *
     * Generated from protobuf field <code>repeated .google.protobuf.DescriptorProto message_type = 4;</code>
     */
    private $message_type;
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.EnumDescriptorProto enum_type = 5;</code>
     */
    private $enum_type;
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.ServiceDescriptorProto service = 6;</code>
     */
    private $service;
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.FieldDescriptorProto extension = 7;</code>
     */
    private $extension;
    /**
     * Generated from protobuf field <code>optional .google.protobuf.FileOptions options = 8;</code>
     */
    protected $options = null;
    /**
     * This field contains optional information about the original source code.
     * You may safely remove this entire field without harming runtime
     * functionality of the descriptors -- the information is needed only by
     * development tools.
     *
     * Generated from protobuf field <code>optional .google.protobuf.SourceCodeInfo source_code_info = 9;</code>
     */
    protected $source_code_info = null;
    /**
     * The syntax of the proto file.
     * The supported values are "proto2", "proto3", and "editions".
     * If `edition` is present, this value must be "editions".
     *
     * Generated from protobuf field <code>optional string syntax = 12;</code>
     */
    protected $syntax = null;
    /**
     * The edition of the proto file, which is an opaque string.
     *
     * Generated from protobuf field <code>optional string edition = 13;</code>
     */
    protected $edition = null;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           file name, relative to root of source tree
     *     @type string $package
     *           e.g. "foo", "foo.bar", etc.
     *     @type array<string>|\Google\Protobuf\Internal\RepeatedField $dependency
     *           Names of files imported by this file.
     *     @type array<int>|\Google\Protobuf\Internal\RepeatedField $public_dependency
     *           Indexes of the public imported files in the dependency list above.
     *     @type array<int>|\Google\Protobuf\Internal\RepeatedField $weak_dependency
     *           Indexes of the weak imported files in the dependency list.
     *           For Google-internal migration only. Do not use.
     *     @type array<\Google\Protobuf\Internal\DescriptorProto>|\Google\Protobuf\Internal\RepeatedField $message_type
     *           All top-level definitions in this file.
     *     @type array<\Google\Protobuf\Internal\EnumDescriptorProto>|\Google\Protobuf\Internal\RepeatedField $enum_type
     *     @type array<\Google\Protobuf\Internal\ServiceDescriptorProto>|\Google\Protobuf\Internal\RepeatedField $service
     *     @type array<\Google\Protobuf\Internal\FieldDescriptorProto>|\Google\Protobuf\Internal\RepeatedField $extension
     *     @type \Google\Protobuf\Internal\FileOptions $options
     *     @type \Google\Protobuf\Internal\SourceCodeInfo $source_code_info
     *           This field contains optional information about the original source code.
     *           You may safely remove this entire field without harming runtime
     *           functionality of the descriptors -- the information is needed only by
     *           development tools.
     *     @type string $syntax
     *           The syntax of the proto file.
     *           The supported values are "proto2", "proto3", and "editions".
     *           If `edition` is present, this value must be "editions".
     *     @type string $edition
     *           The edition of the proto file, which is an opaque string.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Protobuf\Internal\Descriptor::initOnce();
        parent::__construct($data);
    }
    /**
     * file name, relative to root of source tree
     *
     * Generated from protobuf field <code>optional string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return isset($this->name) ? $this->name : '';
    }
    public function hasName()
    {
        return isset($this->name);
    }
    public function clearName()
    {
        unset($this->name);
    }
    /**
     * file name, relative to root of source tree
     *
     * Generated from protobuf field <code>optional string name = 1;</code>
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
     * e.g. "foo", "foo.bar", etc.
     *
     * Generated from protobuf field <code>optional string package = 2;</code>
     * @return string
     */
    public function getPackage()
    {
        return isset($this->package) ? $this->package : '';
    }
    public function hasPackage()
    {
        return isset($this->package);
    }
    public function clearPackage()
    {
        unset($this->package);
    }
    /**
     * e.g. "foo", "foo.bar", etc.
     *
     * Generated from protobuf field <code>optional string package = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setPackage($var)
    {
        GPBUtil::checkString($var, True);
        $this->package = $var;
        return $this;
    }
    /**
     * Names of files imported by this file.
     *
     * Generated from protobuf field <code>repeated string dependency = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDependency()
    {
        return $this->dependency;
    }
    /**
     * Names of files imported by this file.
     *
     * Generated from protobuf field <code>repeated string dependency = 3;</code>
     * @param array<string>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDependency($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->dependency = $arr;
        return $this;
    }
    /**
     * Indexes of the public imported files in the dependency list above.
     *
     * Generated from protobuf field <code>repeated int32 public_dependency = 10;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPublicDependency()
    {
        return $this->public_dependency;
    }
    /**
     * Indexes of the public imported files in the dependency list above.
     *
     * Generated from protobuf field <code>repeated int32 public_dependency = 10;</code>
     * @param array<int>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPublicDependency($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::INT32);
        $this->public_dependency = $arr;
        return $this;
    }
    /**
     * Indexes of the weak imported files in the dependency list.
     * For Google-internal migration only. Do not use.
     *
     * Generated from protobuf field <code>repeated int32 weak_dependency = 11;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getWeakDependency()
    {
        return $this->weak_dependency;
    }
    /**
     * Indexes of the weak imported files in the dependency list.
     * For Google-internal migration only. Do not use.
     *
     * Generated from protobuf field <code>repeated int32 weak_dependency = 11;</code>
     * @param array<int>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setWeakDependency($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::INT32);
        $this->weak_dependency = $arr;
        return $this;
    }
    /**
     * All top-level definitions in this file.
     *
     * Generated from protobuf field <code>repeated .google.protobuf.DescriptorProto message_type = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getMessageType()
    {
        return $this->message_type;
    }
    /**
     * All top-level definitions in this file.
     *
     * Generated from protobuf field <code>repeated .google.protobuf.DescriptorProto message_type = 4;</code>
     * @param array<\Google\Protobuf\Internal\DescriptorProto>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setMessageType($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Protobuf\Internal\DescriptorProto::class);
        $this->message_type = $arr;
        return $this;
    }
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.EnumDescriptorProto enum_type = 5;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getEnumType()
    {
        return $this->enum_type;
    }
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.EnumDescriptorProto enum_type = 5;</code>
     * @param array<\Google\Protobuf\Internal\EnumDescriptorProto>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setEnumType($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Protobuf\Internal\EnumDescriptorProto::class);
        $this->enum_type = $arr;
        return $this;
    }
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.ServiceDescriptorProto service = 6;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getService()
    {
        return $this->service;
    }
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.ServiceDescriptorProto service = 6;</code>
     * @param array<\Google\Protobuf\Internal\ServiceDescriptorProto>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setService($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Protobuf\Internal\ServiceDescriptorProto::class);
        $this->service = $arr;
        return $this;
    }
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.FieldDescriptorProto extension = 7;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getExtension()
    {
        return $this->extension;
    }
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.FieldDescriptorProto extension = 7;</code>
     * @param array<\Google\Protobuf\Internal\FieldDescriptorProto>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setExtension($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Protobuf\Internal\FieldDescriptorProto::class);
        $this->extension = $arr;
        return $this;
    }
    /**
     * Generated from protobuf field <code>optional .google.protobuf.FileOptions options = 8;</code>
     * @return \Google\Protobuf\Internal\FileOptions|null
     */
    public function getOptions()
    {
        return $this->options;
    }
    public function hasOptions()
    {
        return isset($this->options);
    }
    public function clearOptions()
    {
        unset($this->options);
    }
    /**
     * Generated from protobuf field <code>optional .google.protobuf.FileOptions options = 8;</code>
     * @param \Google\Protobuf\Internal\FileOptions $var
     * @return $this
     */
    public function setOptions($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Internal\FileOptions::class);
        $this->options = $var;
        return $this;
    }
    /**
     * This field contains optional information about the original source code.
     * You may safely remove this entire field without harming runtime
     * functionality of the descriptors -- the information is needed only by
     * development tools.
     *
     * Generated from protobuf field <code>optional .google.protobuf.SourceCodeInfo source_code_info = 9;</code>
     * @return \Google\Protobuf\Internal\SourceCodeInfo|null
     */
    public function getSourceCodeInfo()
    {
        return $this->source_code_info;
    }
    public function hasSourceCodeInfo()
    {
        return isset($this->source_code_info);
    }
    public function clearSourceCodeInfo()
    {
        unset($this->source_code_info);
    }
    /**
     * This field contains optional information about the original source code.
     * You may safely remove this entire field without harming runtime
     * functionality of the descriptors -- the information is needed only by
     * development tools.
     *
     * Generated from protobuf field <code>optional .google.protobuf.SourceCodeInfo source_code_info = 9;</code>
     * @param \Google\Protobuf\Internal\SourceCodeInfo $var
     * @return $this
     */
    public function setSourceCodeInfo($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Internal\SourceCodeInfo::class);
        $this->source_code_info = $var;
        return $this;
    }
    /**
     * The syntax of the proto file.
     * The supported values are "proto2", "proto3", and "editions".
     * If `edition` is present, this value must be "editions".
     *
     * Generated from protobuf field <code>optional string syntax = 12;</code>
     * @return string
     */
    public function getSyntax()
    {
        return isset($this->syntax) ? $this->syntax : '';
    }
    public function hasSyntax()
    {
        return isset($this->syntax);
    }
    public function clearSyntax()
    {
        unset($this->syntax);
    }
    /**
     * The syntax of the proto file.
     * The supported values are "proto2", "proto3", and "editions".
     * If `edition` is present, this value must be "editions".
     *
     * Generated from protobuf field <code>optional string syntax = 12;</code>
     * @param string $var
     * @return $this
     */
    public function setSyntax($var)
    {
        GPBUtil::checkString($var, True);
        $this->syntax = $var;
        return $this;
    }
    /**
     * The edition of the proto file, which is an opaque string.
     *
     * Generated from protobuf field <code>optional string edition = 13;</code>
     * @return string
     */
    public function getEdition()
    {
        return isset($this->edition) ? $this->edition : '';
    }
    public function hasEdition()
    {
        return isset($this->edition);
    }
    public function clearEdition()
    {
        unset($this->edition);
    }
    /**
     * The edition of the proto file, which is an opaque string.
     *
     * Generated from protobuf field <code>optional string edition = 13;</code>
     * @param string $var
     * @return $this
     */
    public function setEdition($var)
    {
        GPBUtil::checkString($var, True);
        $this->edition = $var;
        return $this;
    }
}
