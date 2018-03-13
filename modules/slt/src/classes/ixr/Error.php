<?php namespace Slt\Classes\Ixr;

/**
 * Error
 *
 * @package IXR
 * @since 1.5.0
 */
class Error
{
    var $code;
    var $message;

	/**
	 * PHP5 constructor.
	 */
    function __construct( $code, $message )
    {
        $this->code = $code;
        $this->message = htmlspecialchars($message);
    }


    function getXml()
    {
        $xml = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$this->code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$this->message}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>

EOD;
        return $xml;
    }
}