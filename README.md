This PHP Etherpad Lite class allows you to easily interact with Etherpad Lite API with PHP.  Etherpad Lite is a collaborative editor provided by the Etherpad Foundation.  http://etherpad.org

#1 Basic usage

    <pre>
    include 'etherpad-lite-client.php';
    $instance = new EtherpadLiteClient('EtherpadFTW,http://beta.etherpad.org/api');
    $revisionCount = $instance->getRevisionsCount('testPad');
    $revisionCount = $revisionCount->revisions;
    echo "Pad has $revisionCount revisions\n\n";
    </pre>

#2 Examples

See it live here http://beta.etherpad.org/example.php

Examples are located in examples.php and example_big.php.  examples.php contains an example for each API call.  example_big.php contains a UI for managing pads.

This example is the most commonly used, the example displays a pads text on the screen:

    <pre>
    $padContents = $instance->getText('testPad');
    echo "Pad text is: <br/><ul>$padContents->text\n\n</ul>";
    echo "End of Pad Text\n\n<hr>";
    </pre>

#3 License

Apache License

#4 Other stuff

The Etherpad Foundation also provides a jQuery plugin for Etherpad Lite.  This can be found at http://etherpad.org/2011/08/14/etherpad-lite-jquery-plugin/
