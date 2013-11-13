<?php

namespace AerialShip\LightSaml\Binding;


class HttpPostTemplate
{
    /** @var  string */
    private $destination;

    /** @var array */
    private $data;


    function __construct($destination, array $data) {
        $this->destination = $destination;
        $this->data = $data;
    }


    function render() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>POST data</title>
</head>
<body onload="document.getElementsByTagName('input')[0].click();">

<noscript>
    <p><strong>Note:</strong> Since your browser does not support JavaScript, you must press the button below once to proceed.</p>
</noscript>

<form method="post" action="<?php print htmlspecialchars($this->destination); ?>">
    <input type="submit" style="display:none;" />

<?php foreach ($this->data['post'] as $name=>$value) {  ?>
    <input type="hidden" name="<?php print htmlspecialchars($name); ?>" value="<?php print htmlspecialchars($value); ?>" />
<?php } ?>

<noscript>
    <input type="submit" value="Submit" />
</noscript>

</form>

<?php
    }


} 