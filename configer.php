<?php

/**
 * Configer - PHP class for create form to comfortable edit php configuration files
 * options of displaying it's sipmle php comments after data 
 * @link https://github.com/vencendor/configer The Configer GitHub project
 * @author Max Uglov <wzcc@mail.ru>
 */
class Configer {

    public $file_name = "";
    public $data = array(); // Data storage from file
    public $atribute; // Atributes of display
    // Minimal numbers of elements for dispaly select input 
    public $optForSelect = 4;
    // True and false values for checkbox usage
    public $trueValues = array("1", 1, "true", true, "yes");
    public $falseValues = array("0", 0, "false", false, "no");

    // Parse atributes of values from config file 
    public function getAtributes() {
        $conf_str = file_get_contents($this->file_name);
        foreach ($this->data as $n => $cf) {
            // First level of array 
            if (preg_match("#" . $n . "[^\r\n]*//([^\n\r]+)[\n\r]#", $conf_str, $m)) {
                $this->atribute[$n] = trim($m[1]);
            }
            if (is_array($cf)) {
                //second level 
                foreach ($cf as $ns => $vs) {
                    if (preg_match("#" . $n . ".*" . $ns . "[^\r\n]*=>[^\r\n]*//([^\n\r]+)[\n\r]#isU", $conf_str, $m)) {
                        $this->atribute[$n . "~" . $ns] = trim($m[1]);
                    }
                }
            }
        }
    }

    //Convert values to 0 or 1 
    public function toBool($val) {
        if (in_array($val, $this->trueValues))
            return 1;
        if (in_array($val, $this->falseValues))
            return 0;

        return false;
    }

    // Return all boolean style values
    public function booleanValues() {
        return array_merge($this->trueValues, $this->falseValues);
    }

    // Write result to file 
    public function save() {

        $conf_str = var_export($this->data, true);
        foreach ($this->data as $n => $v) {

            // Insert back values atribute first level 
            if (isset($this->atribute[$n])) {
                $conf_str = preg_replace("#([^\n\r]*" . $n . "[^\n\r]*)[\n\r]#is", "\\1//" . $this->atribute[$n] . "\n", $conf_str);
            }

            if (is_array($v)) {
                // Insert atributes second level 
                foreach ($v as $ns => $vs) {
                    if (isset($this->atribute[$n . "~" . $ns])) {
                        $conf_str = preg_replace("#([^\n\r]*" . $n . ".*" . $ns . "[^\r\n]*=>[^\r\n]*)[\n\r]#isU", "\\1//" . $this->atribute[$n . "~" . $ns] . "\n", $conf_str);
                    }
                }
            }
        }
        file_put_contents($this->file_name, "<?php \n return " . $conf_str . " \n ?>");
    }

    // Display input for value 
    public function renderInput($title, $name_parent, $name_var = false, $data_var = false, $options = false) {

        $inputStr = "";
        $input_name = "config[" . $name_parent . "]" . ($name_var !== false ? "[" . $name_var . "]" : "");

        // Simple text input 
        if (!$options or ! in_array($data_var, $options['data'])) {
            $inputStr = "<span> " . $title . " </span><input type='text' name='" . $input_name . "'  value='" . $data_var . "' />";
        } else {
            //CHECKBOX input 
            if ($options['type'] === "checkbox") {
                if (in_array($data_var, $this->booleanValues())) {
                    $inputStr = "<input type='checkbox' " . ($data_var ? "checked='checked'" : "") . " name='" . $input_name . "'  /> <span> " . $title . " </span>";
                } else {
                    $inputStr = "<span> " . $title . " </span><input type='text' name='" . $input_name . "'  value='" . $data_var . "' />";
                }
            }
            //RADIO inputs 
            if ($options['type'] === "radio") {
                foreach ($options['data'] as $n => $v) {
                    $inputStr .= "<input type='radio' value='" . $v . "' " . ( $data_var == $v ? "checked='checked'" : "" ) . " name='" . $input_name . "'  /> <span> " . ( isset($options['labels'][$n]) ? $options['labels'][$n] : $v ) . " </span>";
                }
                $inputStr = "<span> " . $title . " </span>" . $inputStr;
            }
            //SELECT input generate
            if ($options['type'] === "select") {
                foreach ($options['data'] as $n => $v) {
                    $inputStr .= "<option value='" . $v . "' " . ($data_var === $v ? "selected='selected'" : "") . " >  " . ( isset($options['labels'][$n]) ? $options['labels'][$n] : $v ) . " </option> ";
                }
                $inputStr = "<span> " . $title . " </span> <select name='" . $input_name . "' > " . $inputStr . "</select>";
            }
        }

        return $inputStr;
    }

    // Parsing options from values atribute 
    public function parseAtribute($atribute) {
        $flags = array();

        $flags['title'] = $atribute;

        //can't delete this value
        if (strpos($atribute, "[static]") !== false) {
            $flags['static'] = true;
        } else {
            $flags['static'] = false;
        }

        //can add values in this sub array
        if (strpos($atribute, "[dinamic]") !== false) {
            $flags['dinamic'] = true;
        }

        //toggled block by default
        if (strpos($atribute, "[hidden]") !== false) {
            $flags['hidden'] = true;
        } else {
            $flags['hidden'] = false;
        }

        //parsing options that can by values for curent input
        //select type of input 
        if (strpos($atribute, "[options") !== false) {
            preg_match("#\[options\|(.+)+\]#", $atribute, $options);

            $options = explode("|", $options[1]);

            if (sizeof($options) == 2) {
                // if have 2 options and both its from boolean type 
                $checkbox = true;
                foreach ($options as $od) {
                    if (!in_array($od, $this->booleanValues(), true)) {
                        $checkbox = false;
                    }
                }
                if ($checkbox) {
                    $flags['options']['type'] = "checkbox";
                } else {
                    $flags['options']['type'] = "radio";
                }
            } elseif (sizeof($options) < $this->optForSelect) {
                $flags['options']['type'] = "radio";
            } else {
                $flags['options']['type'] = "select";
            }

            $flags['options']['data'] = $options;


            // parse labels for values and clear data
            foreach ($options as $n => $v) {
                if (preg_match("#(.*)\((.*)\)#", $v, $m)) {
                    $flags['options']['data'][$n] = trim($m[1]);
                    $flags['options']['labels'][$n] = $m[2];
                }
            }
        } else {
            $flags['options'] = false;
        }

        // clear title of input 
        $flags['title'] = trim(preg_replace("#\[[^\[\]]*\]#", "", $flags['title']));

        return $flags;
    }

    // boolean data for checkboxes converting
    public function checkboxFilter($atr, $val) {
        $ret = $val;
        if (trim($val) === "" or ! isset($val) or trim($val) === "on") {
            $opt = $this->parseAtribute($atr);

            if ($opt['options']['type'] === "checkbox") {
                if ($ret === "on")
                    $ret = true;

                if ($this->toBool($ret) === $this->toBool($opt['options']['data'][0])) {
                    $ret = $opt['options']['data'][0];
                } else {
                    $ret = $opt['options']['data'][1];
                }
            }
        }
        return $ret;
    }

    public function __construct($file_name) {
        if (!is_file($file_name))
            return false;

        //create backup on first launch
        if (!is_file($file_name . ".bak"))
            copy($file_name, $file_name . ".bak");

        // read data
        $this->file_name = $file_name;
        $this->data = require $this->file_name;

        $this->getAtributes();

        //process form submit
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            // restore default 
            if (isset($_POST['restore_default'])) {
                unlink($file_name);
                copy($file_name . ".bak", $file_name);
            }

            if (isset($_POST['config']) and is_array($_POST['config'])) {


                //check data if its checkbox values for first and second level of values
                foreach ($this->atribute as $n => $v) {
                    if (strpos($n, "~") !== false) {
                        $name = explode("~", $n);

                        if (!isset($_POST['config'][$name[0]][$name[1]]) or $_POST['config'][$name[0]][$name[1]] === "on") {
                            if (!isset($_POST['config'][$name[0]][$name[1]]))
                                $_POST['config'][$name[0]][$name[1]] = 0;

                            $_POST['config'][$name[0]][$name[1]] = $this->checkboxFilter($v, $_POST['config'][$name[0]][$name[1]]);
                        }
                    } else {
                        if (!isset($_POST['config'][$n]) or $_POST['config'][$n] === "on") {
                            if (!isset($_POST['config'][$n]))
                                $_POST['config'][$n] = 0;
                            $_POST['config'][$n] = $this->checkboxFilter($v, $_POST['config'][$n]);
                        }
                    }
                }

                $this->data = array_merge($this->data, $_POST['config']);

                $this->save();
            }
        }
    }

    function showForm() {
        ?>

        <script>
            if (!window.jQuery) {

                document.write(unescape('<script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js">%3C/script%3E'));

            }

        </script>
        <style> 
            legend{
                cursor: pointer;
            }
        </style>

        <form name='restore'> 
            <input type='hidden' name='restore_default' value='do' /> 
            <input type='submit' value='Restore default' />
        </form>

        <form class="form" id='configForm' method='post'>
        <?php

        foreach ($this->data as $n => $d_val) {
            if (isset($this->atribute[$n])) {

                $flags['dinamic'] = false;
                $flags = $this->parseAtribute($this->atribute[$n]);

                if (is_array($d_val)) {
                    echo "<fieldset><legend>" . $flags['title'] . "</legend>";
                    echo "<div class='field' " . ($flags['hidden'] ? "style='display:none'" : "") . "> ";

                    foreach ($d_val as $nc => $vc) {

                        if (isset($this->atribute[$n . "~" . $nc])) {
                            $flags = array_merge($flags, $this->parseAtribute($this->atribute[$n . "~" . $nc]));
                        } else {
                            $flags['title'] = $nc;
                        }

                        echo "<div>" . $this->renderInput($flags['title'], $n, $nc, $vc, $flags['options']);

                        if (isset($flags['dinamic']) and $flags['dinamic'] and ! $flags['static']) {
                            echo "<a class='icon-remove' href='javascript:void(0)' onclick='removeOption(this)' >Del</a>";
                        }
                        echo "</div>";

                        if (isset($flags['dinamic']) and $flags['dinamic']) {
                            $flags['static'] = false;
                        }
                    }
                    if (isset($flags['dinamic']) and $flags['dinamic'] and ! $flags['static']) {
                        echo "<a class='icon-plus' href='javascript:void(0)' onclick='addOption(this,\"" . $n . "\")' >Add</a>";
                    }
                    echo "</div></fieldset>";
                } else {
                    echo "<div>";
                    echo $this->renderInput($flags['title'], $n, false, $d_val, $flags['options']);
                    echo "</div>";
                }
            }
        }
        ?>
            <input type='submit' class='btn btn-info' value='Save' />
        </form>

        <script>

            function addOption(t, name) {

                var optList = $(t.parentNode).find('div');
                var opt = parseInt($(optList[optList.length - 1]).find('span').html());

                $("<div><span> " + (opt + 1) + " </span><input type='text' name='config[" + name + "][" + (opt + 1) + "]'  value='' /><a class='icon-remove' href='javascript:void(0)' onclick='removeOption(this)' >Del</a></div>").insertBefore(t);

            }
            function removeOption(t) {
                $(t.parentNode).find('input').attr('value', '~#deleted#~');
                $(t.parentNode).remove();
            }

            $('legend').click(function () {
                $(this).parent().find('div.field').slideToggle();
            });
        </script>

    <?php

    }

}
?>