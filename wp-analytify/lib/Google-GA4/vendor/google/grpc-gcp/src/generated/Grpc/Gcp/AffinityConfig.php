<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: grpc_gcp.proto
namespace Grpc\Gcp;

use Google\Protobuf\Internal\GPBUtil;
/**
 * Generated from protobuf message <code>grpc.gcp.AffinityConfig</code>
 */
class AffinityConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * The affinity command applies on the selected gRPC methods.
     *
     * Generated from protobuf field <code>.grpc.gcp.AffinityConfig.Command command = 2;</code>
     */
    private $command = 0;
    /**
     * The field path of the affinity key in the request/response message.
     * For example: "f.a", "f.b.d", etc.
     *
     * Generated from protobuf field <code>string affinity_key = 3;</code>
     */
    private $affinity_key = '';
    public function __construct()
    {
        \Analytify\GPBMetadata\GrpcGcp::initOnce();
        parent::__construct();
    }
    /**
     * The affinity command applies on the selected gRPC methods.
     *
     * Generated from protobuf field <code>.grpc.gcp.AffinityConfig.Command command = 2;</code>
     * @return int
     */
    public function getCommand()
    {
        return $this->command;
    }
    /**
     * The affinity command applies on the selected gRPC methods.
     *
     * Generated from protobuf field <code>.grpc.gcp.AffinityConfig.Command command = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setCommand($var)
    {
        GPBUtil::checkEnum($var, \Grpc\Gcp\AffinityConfig_Command::class);
        $this->command = $var;
        return $this;
    }
    /**
     * The field path of the affinity key in the request/response message.
     * For example: "f.a", "f.b.d", etc.
     *
     * Generated from protobuf field <code>string affinity_key = 3;</code>
     * @return string
     */
    public function getAffinityKey()
    {
        return $this->affinity_key;
    }
    /**
     * The field path of the affinity key in the request/response message.
     * For example: "f.a", "f.b.d", etc.
     *
     * Generated from protobuf field <code>string affinity_key = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setAffinityKey($var)
    {
        GPBUtil::checkString($var, \true);
        $this->affinity_key = $var;
        return $this;
    }
}