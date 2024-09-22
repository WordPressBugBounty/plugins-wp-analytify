<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/http.proto
namespace Google\Api;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * `HttpRule` defines the mapping of an RPC method to one or more HTTP
 * REST API methods. The mapping specifies how different portions of the RPC
 * request message are mapped to URL path, URL query parameters, and
 * HTTP request body. The mapping is typically specified as an
 * `google.api.http` annotation on the RPC method,
 * see "google/api/annotations.proto" for details.
 * The mapping consists of a field specifying the path template and
 * method kind.  The path template can refer to fields in the request
 * message, as in the example below which describes a REST GET
 * operation on a resource collection of messages:
 *     service Messaging {
 *       rpc GetMessage(GetMessageRequest) returns (Message) {
 *         option (google.api.http).get = "/v1/messages/{message_id}/{sub.subfield}";
 *       }
 *     }
 *     message GetMessageRequest {
 *       message SubMessage {
 *         string subfield = 1;
 *       }
 *       string message_id = 1; // mapped to the URL
 *       SubMessage sub = 2;    // `sub.subfield` is url-mapped
 *     }
 *     message Message {
 *       string text = 1; // content of the resource
 *     }
 * The same http annotation can alternatively be expressed inside the
 * `GRPC API Configuration` YAML file.
 *     http:
 *       rules:
 *         - selector: <proto_package_name>.Messaging.GetMessage
 *           get: /v1/messages/{message_id}/{sub.subfield}
 * This definition enables an automatic, bidrectional mapping of HTTP
 * JSON to RPC. Example:
 * HTTP | RPC
 * -----|-----
 * `GET /v1/messages/123456/foo`  | `GetMessage(message_id: "123456" sub: SubMessage(subfield: "foo"))`
 * In general, not only fields but also field paths can be referenced
 * from a path pattern. Fields mapped to the path pattern cannot be
 * repeated and must have a primitive (non-message) type.
 * Any fields in the request message which are not bound by the path
 * pattern automatically become (optional) HTTP query
 * parameters. Assume the following definition of the request message:
 *     service Messaging {
 *       rpc GetMessage(GetMessageRequest) returns (Message) {
 *         option (google.api.http).get = "/v1/messages/{message_id}";
 *       }
 *     }
 *     message GetMessageRequest {
 *       message SubMessage {
 *         string subfield = 1;
 *       }
 *       string message_id = 1; // mapped to the URL
 *       int64 revision = 2;    // becomes a parameter
 *       SubMessage sub = 3;    // `sub.subfield` becomes a parameter
 *     }
 * This enables a HTTP JSON to RPC mapping as below:
 * HTTP | RPC
 * -----|-----
 * `GET /v1/messages/123456?revision=2&sub.subfield=foo` | `GetMessage(message_id: "123456" revision: 2 sub: SubMessage(subfield: "foo"))`
 * Note that fields which are mapped to HTTP parameters must have a
 * primitive type or a repeated primitive type. Message types are not
 * allowed. In the case of a repeated type, the parameter can be
 * repeated in the URL, as in `...?param=A&param=B`.
 * For HTTP method kinds which allow a request body, the `body` field
 * specifies the mapping. Consider a REST update method on the
 * message resource collection:
 *     service Messaging {
 *       rpc UpdateMessage(UpdateMessageRequest) returns (Message) {
 *         option (google.api.http) = {
 *           put: "/v1/messages/{message_id}"
 *           body: "message"
 *         };
 *       }
 *     }
 *     message UpdateMessageRequest {
 *       string message_id = 1; // mapped to the URL
 *       Message message = 2;   // mapped to the body
 *     }
 * The following HTTP JSON to RPC mapping is enabled, where the
 * representation of the JSON in the request body is determined by
 * protos JSON encoding:
 * HTTP | RPC
 * -----|-----
 * `PUT /v1/messages/123456 { "text": "Hi!" }` | `UpdateMessage(message_id: "123456" message { text: "Hi!" })`
 * The special name `*` can be used in the body mapping to define that
 * every field not bound by the path template should be mapped to the
 * request body.  This enables the following alternative definition of
 * the update method:
 *     service Messaging {
 *       rpc UpdateMessage(Message) returns (Message) {
 *         option (google.api.http) = {
 *           put: "/v1/messages/{message_id}"
 *           body: "*"
 *         };
 *       }
 *     }
 *     message Message {
 *       string message_id = 1;
 *       string text = 2;
 *     }
 * The following HTTP JSON to RPC mapping is enabled:
 * HTTP | RPC
 * -----|-----
 * `PUT /v1/messages/123456 { "text": "Hi!" }` | `UpdateMessage(message_id: "123456" text: "Hi!")`
 * Note that when using `*` in the body mapping, it is not possible to
 * have HTTP parameters, as all fields not bound by the path end in
 * the body. This makes this option more rarely used in practice of
 * defining REST APIs. The common usage of `*` is in custom methods
 * which don't use the URL at all for transferring data.
 * It is possible to define multiple HTTP methods for one RPC by using
 * the `additional_bindings` option. Example:
 *     service Messaging {
 *       rpc GetMessage(GetMessageRequest) returns (Message) {
 *         option (google.api.http) = {
 *           get: "/v1/messages/{message_id}"
 *           additional_bindings {
 *             get: "/v1/users/{user_id}/messages/{message_id}"
 *           }
 *         };
 *       }
 *     }
 *     message GetMessageRequest {
 *       string message_id = 1;
 *       string user_id = 2;
 *     }
 * This enables the following two alternative HTTP JSON to RPC
 * mappings:
 * HTTP | RPC
 * -----|-----
 * `GET /v1/messages/123456` | `GetMessage(message_id: "123456")`
 * `GET /v1/users/me/messages/123456` | `GetMessage(user_id: "me" message_id: "123456")`
 * # Rules for HTTP mapping
 * The rules for mapping HTTP path, query parameters, and body fields
 * to the request message are as follows:
 * 1. The `body` field specifies either `*` or a field path, or is
 *    omitted. If omitted, it indicates there is no HTTP request body.
 * 2. Leaf fields (recursive expansion of nested messages in the
 *    request) can be classified into three types:
 *     (a) Matched in the URL template.
 *     (b) Covered by body (if body is `*`, everything except (a) fields;
 *         else everything under the body field)
 *     (c) All other fields.
 * 3. URL query parameters found in the HTTP request are mapped to (c) fields.
 * 4. Any body sent with an HTTP request can contain only (b) fields.
 * The syntax of the path template is as follows:
 *     Template = "/" Segments [ Verb ] ;
 *     Segments = Segment { "/" Segment } ;
 *     Segment  = "*" | "**" | LITERAL | Variable ;
 *     Variable = "{" FieldPath [ "=" Segments ] "}" ;
 *     FieldPath = IDENT { "." IDENT } ;
 *     Verb     = ":" LITERAL ;
 * The syntax `*` matches a single path segment. The syntax `**` matches zero
 * or more path segments, which must be the last part of the path except the
 * `Verb`. The syntax `LITERAL` matches literal text in the path.
 * The syntax `Variable` matches part of the URL path as specified by its
 * template. A variable template must not contain other variables. If a variable
 * matches a single path segment, its template may be omitted, e.g. `{var}`
 * is equivalent to `{var=*}`.
 * If a variable contains exactly one path segment, such as `"{var}"` or
 * `"{var=*}"`, when such a variable is expanded into a URL path, all characters
 * except `[-_.~0-9a-zA-Z]` are percent-encoded. Such variables show up in the
 * Discovery Document as `{var}`.
 * If a variable contains one or more path segments, such as `"{var=foo/&#42;}"`
 * or `"{var=**}"`, when such a variable is expanded into a URL path, all
 * characters except `[-_.~/0-9a-zA-Z]` are percent-encoded. Such variables
 * show up in the Discovery Document as `{+var}`.
 * NOTE: While the single segment variable matches the semantics of
 * [RFC 6570](https://tools.ietf.org/html/rfc6570) Section 3.2.2
 * Simple String Expansion, the multi segment variable **does not** match
 * RFC 6570 Reserved Expansion. The reason is that the Reserved Expansion
 * does not expand special characters like `?` and `#`, which would lead
 * to invalid URLs.
 * NOTE: the field paths in variables and in the `body` must not refer to
 * repeated fields or map fields.
 *
 * Generated from protobuf message <code>google.api.HttpRule</code>
 */
class HttpRule extends \Google\Protobuf\Internal\Message
{
    /**
     * Selects methods to which this rule applies.
     * Refer to [selector][google.api.DocumentationRule.selector] for syntax details.
     *
     * Generated from protobuf field <code>string selector = 1;</code>
     */
    private $selector = '';
    /**
     * The name of the request field whose value is mapped to the HTTP body, or
     * `*` for mapping all fields not captured by the path pattern to the HTTP
     * body. NOTE: the referred field must not be a repeated field and must be
     * present at the top-level of request message type.
     *
     * Generated from protobuf field <code>string body = 7;</code>
     */
    private $body = '';
    /**
     * Optional. The name of the response field whose value is mapped to the HTTP
     * body of response. Other response fields are ignored. When
     * not set, the response message will be used as HTTP body of response.
     *
     * Generated from protobuf field <code>string response_body = 12;</code>
     */
    private $response_body = '';
    /**
     * Additional HTTP bindings for the selector. Nested bindings must
     * not contain an `additional_bindings` field themselves (that is,
     * the nesting may only be one level deep).
     *
     * Generated from protobuf field <code>repeated .google.api.HttpRule additional_bindings = 11;</code>
     */
    private $additional_bindings;
    protected $pattern;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $selector
     *           Selects methods to which this rule applies.
     *           Refer to [selector][google.api.DocumentationRule.selector] for syntax details.
     *     @type string $get
     *           Used for listing and getting information about resources.
     *     @type string $put
     *           Used for updating a resource.
     *     @type string $post
     *           Used for creating a resource.
     *     @type string $delete
     *           Used for deleting a resource.
     *     @type string $patch
     *           Used for updating a resource.
     *     @type \Google\Api\CustomHttpPattern $custom
     *           The custom pattern is used for specifying an HTTP method that is not
     *           included in the `pattern` field, such as HEAD, or "*" to leave the
     *           HTTP method unspecified for this rule. The wild-card rule is useful
     *           for services that provide content to Web (HTML) clients.
     *     @type string $body
     *           The name of the request field whose value is mapped to the HTTP body, or
     *           `*` for mapping all fields not captured by the path pattern to the HTTP
     *           body. NOTE: the referred field must not be a repeated field and must be
     *           present at the top-level of request message type.
     *     @type string $response_body
     *           Optional. The name of the response field whose value is mapped to the HTTP
     *           body of response. Other response fields are ignored. When
     *           not set, the response message will be used as HTTP body of response.
     *     @type \Google\Api\HttpRule[]|\Google\Protobuf\Internal\RepeatedField $additional_bindings
     *           Additional HTTP bindings for the selector. Nested bindings must
     *           not contain an `additional_bindings` field themselves (that is,
     *           the nesting may only be one level deep).
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Api\Http::initOnce();
        parent::__construct($data);
    }
    /**
     * Selects methods to which this rule applies.
     * Refer to [selector][google.api.DocumentationRule.selector] for syntax details.
     *
     * Generated from protobuf field <code>string selector = 1;</code>
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }
    /**
     * Selects methods to which this rule applies.
     * Refer to [selector][google.api.DocumentationRule.selector] for syntax details.
     *
     * Generated from protobuf field <code>string selector = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSelector($var)
    {
        GPBUtil::checkString($var, True);
        $this->selector = $var;
        return $this;
    }
    /**
     * Used for listing and getting information about resources.
     *
     * Generated from protobuf field <code>string get = 2;</code>
     * @return string
     */
    public function getGet()
    {
        return $this->readOneof(2);
    }
    /**
     * Used for listing and getting information about resources.
     *
     * Generated from protobuf field <code>string get = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setGet($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(2, $var);
        return $this;
    }
    /**
     * Used for updating a resource.
     *
     * Generated from protobuf field <code>string put = 3;</code>
     * @return string
     */
    public function getPut()
    {
        return $this->readOneof(3);
    }
    /**
     * Used for updating a resource.
     *
     * Generated from protobuf field <code>string put = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setPut($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(3, $var);
        return $this;
    }
    /**
     * Used for creating a resource.
     *
     * Generated from protobuf field <code>string post = 4;</code>
     * @return string
     */
    public function getPost()
    {
        return $this->readOneof(4);
    }
    /**
     * Used for creating a resource.
     *
     * Generated from protobuf field <code>string post = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setPost($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(4, $var);
        return $this;
    }
    /**
     * Used for deleting a resource.
     *
     * Generated from protobuf field <code>string delete = 5;</code>
     * @return string
     */
    public function getDelete()
    {
        return $this->readOneof(5);
    }
    /**
     * Used for deleting a resource.
     *
     * Generated from protobuf field <code>string delete = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setDelete($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(5, $var);
        return $this;
    }
    /**
     * Used for updating a resource.
     *
     * Generated from protobuf field <code>string patch = 6;</code>
     * @return string
     */
    public function getPatch()
    {
        return $this->readOneof(6);
    }
    /**
     * Used for updating a resource.
     *
     * Generated from protobuf field <code>string patch = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setPatch($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(6, $var);
        return $this;
    }
    /**
     * The custom pattern is used for specifying an HTTP method that is not
     * included in the `pattern` field, such as HEAD, or "*" to leave the
     * HTTP method unspecified for this rule. The wild-card rule is useful
     * for services that provide content to Web (HTML) clients.
     *
     * Generated from protobuf field <code>.google.api.CustomHttpPattern custom = 8;</code>
     * @return \Google\Api\CustomHttpPattern
     */
    public function getCustom()
    {
        return $this->readOneof(8);
    }
    /**
     * The custom pattern is used for specifying an HTTP method that is not
     * included in the `pattern` field, such as HEAD, or "*" to leave the
     * HTTP method unspecified for this rule. The wild-card rule is useful
     * for services that provide content to Web (HTML) clients.
     *
     * Generated from protobuf field <code>.google.api.CustomHttpPattern custom = 8;</code>
     * @param \Google\Api\CustomHttpPattern $var
     * @return $this
     */
    public function setCustom($var)
    {
        GPBUtil::checkMessage($var, \Google\Api\CustomHttpPattern::class);
        $this->writeOneof(8, $var);
        return $this;
    }
    /**
     * The name of the request field whose value is mapped to the HTTP body, or
     * `*` for mapping all fields not captured by the path pattern to the HTTP
     * body. NOTE: the referred field must not be a repeated field and must be
     * present at the top-level of request message type.
     *
     * Generated from protobuf field <code>string body = 7;</code>
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
    /**
     * The name of the request field whose value is mapped to the HTTP body, or
     * `*` for mapping all fields not captured by the path pattern to the HTTP
     * body. NOTE: the referred field must not be a repeated field and must be
     * present at the top-level of request message type.
     *
     * Generated from protobuf field <code>string body = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setBody($var)
    {
        GPBUtil::checkString($var, True);
        $this->body = $var;
        return $this;
    }
    /**
     * Optional. The name of the response field whose value is mapped to the HTTP
     * body of response. Other response fields are ignored. When
     * not set, the response message will be used as HTTP body of response.
     *
     * Generated from protobuf field <code>string response_body = 12;</code>
     * @return string
     */
    public function getResponseBody()
    {
        return $this->response_body;
    }
    /**
     * Optional. The name of the response field whose value is mapped to the HTTP
     * body of response. Other response fields are ignored. When
     * not set, the response message will be used as HTTP body of response.
     *
     * Generated from protobuf field <code>string response_body = 12;</code>
     * @param string $var
     * @return $this
     */
    public function setResponseBody($var)
    {
        GPBUtil::checkString($var, True);
        $this->response_body = $var;
        return $this;
    }
    /**
     * Additional HTTP bindings for the selector. Nested bindings must
     * not contain an `additional_bindings` field themselves (that is,
     * the nesting may only be one level deep).
     *
     * Generated from protobuf field <code>repeated .google.api.HttpRule additional_bindings = 11;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAdditionalBindings()
    {
        return $this->additional_bindings;
    }
    /**
     * Additional HTTP bindings for the selector. Nested bindings must
     * not contain an `additional_bindings` field themselves (that is,
     * the nesting may only be one level deep).
     *
     * Generated from protobuf field <code>repeated .google.api.HttpRule additional_bindings = 11;</code>
     * @param \Google\Api\HttpRule[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setAdditionalBindings($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Api\HttpRule::class);
        $this->additional_bindings = $arr;
        return $this;
    }
    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->whichOneof("pattern");
    }
}
