<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/type/color.proto
namespace Google\Type;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Represents a color in the RGBA color space. This representation is designed
 * for simplicity of conversion to/from color representations in various
 * languages over compactness; for example, the fields of this representation
 * can be trivially provided to the constructor of "java.awt.Color" in Java; it
 * can also be trivially provided to UIColor's "+colorWithRed:green:blue:alpha"
 * method in iOS; and, with just a little work, it can be easily formatted into
 * a CSS "rgba()" string in JavaScript, as well.
 * Note: this proto does not carry information about the absolute color space
 * that should be used to interpret the RGB value (e.g. sRGB, Adobe RGB,
 * DCI-P3, BT.2020, etc.). By default, applications SHOULD assume the sRGB color
 * space.
 * Example (Java):
 *      import com.google.type.Color;
 *      // ...
 *      public static java.awt.Color fromProto(Color protocolor) {
 *        float alpha = protocolor.hasAlpha()
 *            ? protocolor.getAlpha().getValue()
 *            : 1.0;
 *        return new java.awt.Color(
 *            protocolor.getRed(),
 *            protocolor.getGreen(),
 *            protocolor.getBlue(),
 *            alpha);
 *      }
 *      public static Color toProto(java.awt.Color color) {
 *        float red = (float) color.getRed();
 *        float green = (float) color.getGreen();
 *        float blue = (float) color.getBlue();
 *        float denominator = 255.0;
 *        Color.Builder resultBuilder =
 *            Color
 *                .newBuilder()
 *                .setRed(red / denominator)
 *                .setGreen(green / denominator)
 *                .setBlue(blue / denominator);
 *        int alpha = color.getAlpha();
 *        if (alpha != 255) {
 *          result.setAlpha(
 *              FloatValue
 *                  .newBuilder()
 *                  .setValue(((float) alpha) / denominator)
 *                  .build());
 *        }
 *        return resultBuilder.build();
 *      }
 *      // ...
 * Example (iOS / Obj-C):
 *      // ...
 *      static UIColor* fromProto(Color* protocolor) {
 *         float red = [protocolor red];
 *         float green = [protocolor green];
 *         float blue = [protocolor blue];
 *         FloatValue* alpha_wrapper = [protocolor alpha];
 *         float alpha = 1.0;
 *         if (alpha_wrapper != nil) {
 *           alpha = [alpha_wrapper value];
 *         }
 *         return [UIColor colorWithRed:red green:green blue:blue alpha:alpha];
 *      }
 *      static Color* toProto(UIColor* color) {
 *          CGFloat red, green, blue, alpha;
 *          if (![color getRed:&red green:&green blue:&blue alpha:&alpha]) {
 *            return nil;
 *          }
 *          Color* result = [[Color alloc] init];
 *          [result setRed:red];
 *          [result setGreen:green];
 *          [result setBlue:blue];
 *          if (alpha <= 0.9999) {
 *            [result setAlpha:floatWrapperWithValue(alpha)];
 *          }
 *          [result autorelease];
 *          return result;
 *     }
 *     // ...
 *  Example (JavaScript):
 *     // ...
 *     var protoToCssColor = function(rgb_color) {
 *        var redFrac = rgb_color.red || 0.0;
 *        var greenFrac = rgb_color.green || 0.0;
 *        var blueFrac = rgb_color.blue || 0.0;
 *        var red = Math.floor(redFrac * 255);
 *        var green = Math.floor(greenFrac * 255);
 *        var blue = Math.floor(blueFrac * 255);
 *        if (!('alpha' in rgb_color)) {
 *           return rgbToCssColor_(red, green, blue);
 *        }
 *        var alphaFrac = rgb_color.alpha.value || 0.0;
 *        var rgbParams = [red, green, blue].join(',');
 *        return ['rgba(', rgbParams, ',', alphaFrac, ')'].join('');
 *     };
 *     var rgbToCssColor_ = function(red, green, blue) {
 *       var rgbNumber = new Number((red << 16) | (green << 8) | blue);
 *       var hexString = rgbNumber.toString(16);
 *       var missingZeros = 6 - hexString.length;
 *       var resultBuilder = ['#'];
 *       for (var i = 0; i < missingZeros; i++) {
 *          resultBuilder.push('0');
 *       }
 *       resultBuilder.push(hexString);
 *       return resultBuilder.join('');
 *     };
 *     // ...
 *
 * Generated from protobuf message <code>google.type.Color</code>
 */
class Color extends \Google\Protobuf\Internal\Message
{
    /**
     * The amount of red in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float red = 1;</code>
     */
    private $red = 0.0;
    /**
     * The amount of green in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float green = 2;</code>
     */
    private $green = 0.0;
    /**
     * The amount of blue in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float blue = 3;</code>
     */
    private $blue = 0.0;
    /**
     * The fraction of this color that should be applied to the pixel. That is,
     * the final pixel color is defined by the equation:
     *   pixel color = alpha * (this color) + (1.0 - alpha) * (background color)
     * This means that a value of 1.0 corresponds to a solid color, whereas
     * a value of 0.0 corresponds to a completely transparent color. This
     * uses a wrapper message rather than a simple float scalar so that it is
     * possible to distinguish between a default value and the value being unset.
     * If omitted, this color object is to be rendered as a solid color
     * (as if the alpha value had been explicitly given with a value of 1.0).
     *
     * Generated from protobuf field <code>.google.protobuf.FloatValue alpha = 4;</code>
     */
    private $alpha = null;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type float $red
     *           The amount of red in the color as a value in the interval [0, 1].
     *     @type float $green
     *           The amount of green in the color as a value in the interval [0, 1].
     *     @type float $blue
     *           The amount of blue in the color as a value in the interval [0, 1].
     *     @type \Google\Protobuf\FloatValue $alpha
     *           The fraction of this color that should be applied to the pixel. That is,
     *           the final pixel color is defined by the equation:
     *             pixel color = alpha * (this color) + (1.0 - alpha) * (background color)
     *           This means that a value of 1.0 corresponds to a solid color, whereas
     *           a value of 0.0 corresponds to a completely transparent color. This
     *           uses a wrapper message rather than a simple float scalar so that it is
     *           possible to distinguish between a default value and the value being unset.
     *           If omitted, this color object is to be rendered as a solid color
     *           (as if the alpha value had been explicitly given with a value of 1.0).
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Type\Color::initOnce();
        parent::__construct($data);
    }
    /**
     * The amount of red in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float red = 1;</code>
     * @return float
     */
    public function getRed()
    {
        return $this->red;
    }
    /**
     * The amount of red in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float red = 1;</code>
     * @param float $var
     * @return $this
     */
    public function setRed($var)
    {
        GPBUtil::checkFloat($var);
        $this->red = $var;
        return $this;
    }
    /**
     * The amount of green in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float green = 2;</code>
     * @return float
     */
    public function getGreen()
    {
        return $this->green;
    }
    /**
     * The amount of green in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float green = 2;</code>
     * @param float $var
     * @return $this
     */
    public function setGreen($var)
    {
        GPBUtil::checkFloat($var);
        $this->green = $var;
        return $this;
    }
    /**
     * The amount of blue in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float blue = 3;</code>
     * @return float
     */
    public function getBlue()
    {
        return $this->blue;
    }
    /**
     * The amount of blue in the color as a value in the interval [0, 1].
     *
     * Generated from protobuf field <code>float blue = 3;</code>
     * @param float $var
     * @return $this
     */
    public function setBlue($var)
    {
        GPBUtil::checkFloat($var);
        $this->blue = $var;
        return $this;
    }
    /**
     * The fraction of this color that should be applied to the pixel. That is,
     * the final pixel color is defined by the equation:
     *   pixel color = alpha * (this color) + (1.0 - alpha) * (background color)
     * This means that a value of 1.0 corresponds to a solid color, whereas
     * a value of 0.0 corresponds to a completely transparent color. This
     * uses a wrapper message rather than a simple float scalar so that it is
     * possible to distinguish between a default value and the value being unset.
     * If omitted, this color object is to be rendered as a solid color
     * (as if the alpha value had been explicitly given with a value of 1.0).
     *
     * Generated from protobuf field <code>.google.protobuf.FloatValue alpha = 4;</code>
     * @return \Google\Protobuf\FloatValue
     */
    public function getAlpha()
    {
        return $this->alpha;
    }
    /**
     * Returns the unboxed value from <code>getAlpha()</code>
     * The fraction of this color that should be applied to the pixel. That is,
     * the final pixel color is defined by the equation:
     *   pixel color = alpha * (this color) + (1.0 - alpha) * (background color)
     * This means that a value of 1.0 corresponds to a solid color, whereas
     * a value of 0.0 corresponds to a completely transparent color. This
     * uses a wrapper message rather than a simple float scalar so that it is
     * possible to distinguish between a default value and the value being unset.
     * If omitted, this color object is to be rendered as a solid color
     * (as if the alpha value had been explicitly given with a value of 1.0).
     *
     * Generated from protobuf field <code>.google.protobuf.FloatValue alpha = 4;</code>
     * @return float|null
     */
    public function getAlphaUnwrapped()
    {
        $wrapper = $this->getAlpha();
        return \is_null($wrapper) ? null : $wrapper->getValue();
    }
    /**
     * The fraction of this color that should be applied to the pixel. That is,
     * the final pixel color is defined by the equation:
     *   pixel color = alpha * (this color) + (1.0 - alpha) * (background color)
     * This means that a value of 1.0 corresponds to a solid color, whereas
     * a value of 0.0 corresponds to a completely transparent color. This
     * uses a wrapper message rather than a simple float scalar so that it is
     * possible to distinguish between a default value and the value being unset.
     * If omitted, this color object is to be rendered as a solid color
     * (as if the alpha value had been explicitly given with a value of 1.0).
     *
     * Generated from protobuf field <code>.google.protobuf.FloatValue alpha = 4;</code>
     * @param \Google\Protobuf\FloatValue $var
     * @return $this
     */
    public function setAlpha($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\FloatValue::class);
        $this->alpha = $var;
        return $this;
    }
    /**
     * Sets the field by wrapping a primitive type in a Google\Protobuf\FloatValue object.
     * The fraction of this color that should be applied to the pixel. That is,
     * the final pixel color is defined by the equation:
     *   pixel color = alpha * (this color) + (1.0 - alpha) * (background color)
     * This means that a value of 1.0 corresponds to a solid color, whereas
     * a value of 0.0 corresponds to a completely transparent color. This
     * uses a wrapper message rather than a simple float scalar so that it is
     * possible to distinguish between a default value and the value being unset.
     * If omitted, this color object is to be rendered as a solid color
     * (as if the alpha value had been explicitly given with a value of 1.0).
     *
     * Generated from protobuf field <code>.google.protobuf.FloatValue alpha = 4;</code>
     * @param float|null $var
     * @return $this
     */
    public function setAlphaUnwrapped($var)
    {
        $wrappedVar = \is_null($var) ? null : new \Google\Protobuf\FloatValue(['value' => $var]);
        return $this->setAlpha($wrappedVar);
    }
}
