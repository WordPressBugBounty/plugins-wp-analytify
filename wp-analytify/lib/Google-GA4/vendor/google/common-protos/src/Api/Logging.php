<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/logging.proto
namespace Google\Api;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Logging configuration of the service.
 * The following example shows how to configure logs to be sent to the
 * producer and consumer projects. In the example, the `activity_history`
 * log is sent to both the producer and consumer projects, whereas the
 * `purchase_history` log is only sent to the producer project.
 *     monitored_resources:
 *     - type: library.googleapis.com/branch
 *       labels:
 *       - key: /city
 *         description: The city where the library branch is located in.
 *       - key: /name
 *         description: The name of the branch.
 *     logs:
 *     - name: activity_history
 *       labels:
 *       - key: /customer_id
 *     - name: purchase_history
 *     logging:
 *       producer_destinations:
 *       - monitored_resource: library.googleapis.com/branch
 *         logs:
 *         - activity_history
 *         - purchase_history
 *       consumer_destinations:
 *       - monitored_resource: library.googleapis.com/branch
 *         logs:
 *         - activity_history
 *
 * Generated from protobuf message <code>google.api.Logging</code>
 */
class Logging extends \Google\Protobuf\Internal\Message
{
    /**
     * Logging configurations for sending logs to the producer project.
     * There can be multiple producer destinations, each one must have a
     * different monitored resource type. A log can be used in at most
     * one producer destination.
     *
     * Generated from protobuf field <code>repeated .google.api.Logging.LoggingDestination producer_destinations = 1;</code>
     */
    private $producer_destinations;
    /**
     * Logging configurations for sending logs to the consumer project.
     * There can be multiple consumer destinations, each one must have a
     * different monitored resource type. A log can be used in at most
     * one consumer destination.
     *
     * Generated from protobuf field <code>repeated .google.api.Logging.LoggingDestination consumer_destinations = 2;</code>
     */
    private $consumer_destinations;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Api\Logging\LoggingDestination[]|\Google\Protobuf\Internal\RepeatedField $producer_destinations
     *           Logging configurations for sending logs to the producer project.
     *           There can be multiple producer destinations, each one must have a
     *           different monitored resource type. A log can be used in at most
     *           one producer destination.
     *     @type \Google\Api\Logging\LoggingDestination[]|\Google\Protobuf\Internal\RepeatedField $consumer_destinations
     *           Logging configurations for sending logs to the consumer project.
     *           There can be multiple consumer destinations, each one must have a
     *           different monitored resource type. A log can be used in at most
     *           one consumer destination.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Api\Logging::initOnce();
        parent::__construct($data);
    }
    /**
     * Logging configurations for sending logs to the producer project.
     * There can be multiple producer destinations, each one must have a
     * different monitored resource type. A log can be used in at most
     * one producer destination.
     *
     * Generated from protobuf field <code>repeated .google.api.Logging.LoggingDestination producer_destinations = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getProducerDestinations()
    {
        return $this->producer_destinations;
    }
    /**
     * Logging configurations for sending logs to the producer project.
     * There can be multiple producer destinations, each one must have a
     * different monitored resource type. A log can be used in at most
     * one producer destination.
     *
     * Generated from protobuf field <code>repeated .google.api.Logging.LoggingDestination producer_destinations = 1;</code>
     * @param \Google\Api\Logging\LoggingDestination[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setProducerDestinations($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Api\Logging\LoggingDestination::class);
        $this->producer_destinations = $arr;
        return $this;
    }
    /**
     * Logging configurations for sending logs to the consumer project.
     * There can be multiple consumer destinations, each one must have a
     * different monitored resource type. A log can be used in at most
     * one consumer destination.
     *
     * Generated from protobuf field <code>repeated .google.api.Logging.LoggingDestination consumer_destinations = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getConsumerDestinations()
    {
        return $this->consumer_destinations;
    }
    /**
     * Logging configurations for sending logs to the consumer project.
     * There can be multiple consumer destinations, each one must have a
     * different monitored resource type. A log can be used in at most
     * one consumer destination.
     *
     * Generated from protobuf field <code>repeated .google.api.Logging.LoggingDestination consumer_destinations = 2;</code>
     * @param \Google\Api\Logging\LoggingDestination[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setConsumerDestinations($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Api\Logging\LoggingDestination::class);
        $this->consumer_destinations = $arr;
        return $this;
    }
}
