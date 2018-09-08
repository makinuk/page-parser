<?php

namespace Makinuk\PageParser;


class Parser
{
    private $html_data = '';
    public $_HtmlFroms = array();
    public $_HttpCookies = array();
    private $_counter = 0;
    private $button_counter = '';

    private function prepareHtmlData($html_data) {
        if ( is_array($html_data) ) {
            $this->html_data = join('', $html_data);
        } else {
            $this->html_data = $html_data;
        }
    }

    public function getParsedFormElements($Number = 0,$withImplode = true,$WithOutDisabled = true) {

        if (isset($this->_HtmlFroms[$Number]["form_elemets"])) {
            foreach ($this->_HtmlFroms[$Number]["form_elemets"] as $key=>$value) {
                if ($WithOutDisabled) {
                    if ($value["status"] == true) {
                        $return[$value["name"]] = urlencode($value["name"])."=".urlencode($value["value"]);
                    }
                }
                else {
                    $return[$value["name"]] = urlencode($value["name"])."=".urlencode($value["value"]);
                }
            }
        }
        if ($withImplode) {
            return (is_array($return) ? implode("&",$return) : "" );;
        }
        else {
            return $return;
        }
    }

    public function createFormElements($Name,$value,$FormId = 0) {
        $this->_HtmlFroms[$FormId]["form_elemets"][$Name]["value"] = $value;
        $this->_HtmlFroms[$FormId]["form_elemets"][$Name]["name"] = $Name;
        $this->_HtmlFroms[$FormId]["form_elemets"][$Name]["status"] = true;
    }

    public function setFormValue($Name,$value,$FormId = 0){

        if (isset($this->_HtmlFroms[$FormId]["form_elemets"][$Name])) {
            $this->_HtmlFroms[$FormId]["form_elemets"][$Name]["value"] = $value;
            $this->_HtmlFroms[$FormId]["form_elemets"][$Name]["status"] = true;
            return true;
        }
        else {
            return false;
        }
    }

    public function getFormValue($Name,$FormId = 0){

        if (isset($this->_HtmlFroms[$FormId]["form_elemets"][$Name])) {
            return $this->_HtmlFroms[$FormId]["form_elemets"][$Name]["value"];
        }
        else {
            return false;
        }
    }

    public function getFormData($Name,$Value,$FormId = 0){

        if (isset($this->_HtmlFroms[$FormId]["form_elemets"][$Name])) {
            return $this->_HtmlFroms[$FormId]["form_elemets"][$Name][$Value];
        }
    }

    public function getFormAction($FormId = 0){

        if (isset($this->_HtmlFroms[$FormId]["form_data"]["action"])) {
            return $this->_HtmlFroms[$FormId]["form_data"]["action"];
        }
        else {
            return false;
        }
    }

    public function setCookieValue($Name,$value){

        if (isset($this->_HttpCookies[$Name])) {
            $this->_HttpCookies[$Name][$Name] = $value;
            return true;
        }
        else {
            return false;
        }
    }

    public function createCookieValue($Name,$value){
        $this->_HttpCookies[$Name] = $value;
        return true;
    }

    public function RemoveFormElement($Name,$FormId = 0){

        if (isset($this->_HtmlFroms[$FormId]["form_elemets"][$Name])) {
            unset($this->_HtmlFroms[$FormId]["form_elemets"][$Name]);
            return true;
        }
        else {
            return false;
        }
    }

    public function RemoveFormElementRegEx($RegEx,$FormId = 0){

        if (is_array($this->_HtmlFroms[$FormId]["form_elemets"])) {
            $filteredKeys = array_filter(array_keys($this->_HtmlFroms[$FormId]["form_elemets"]), function ($value) use ($RegEx) {return (preg_match($RegEx,$value));});
            if (is_array($filteredKeys)) {
                foreach ($filteredKeys as $Key) {
                    unset($this->_HtmlFroms[$FormId]["form_elemets"][$Key]);
                }
            }
            return true;
        }else {
            return false;
        }
    }

    public function getElementByValue($Data,$FormId = 0){
        $filteredKeys = Array();
        if (is_array($this->_HtmlFroms[$FormId]["form_elemets"])) {
            $filteredKeys = array_filter($this->_HtmlFroms[$FormId]["form_elemets"], function ($value) use ($Data) {return ($value["value"] == $Data);});
        }
        return $filteredKeys;
    }

    public function getParsetCookies($withImplode = true){

        $CookieList = array();
        if (is_array($this->_HttpCookies)) {
            foreach ($this->_HttpCookies as $ckey=>$cvalue) {
                $CookieList[$ckey] = $ckey."=".$cvalue;
            }
        }

        if($withImplode) {
            return implode("; ",$CookieList);
        }
        else {
            return $CookieList;
        }
    }


    private function getElementsData($form,$GetButtons = true,$GetSubmit = true) {

        if ( preg_match_all("/<input[^>]*type=[\"']?(text|input)[\"']? ?[^>]*>/iU", $form, $texts) ) {
            foreach ( $texts[0] as $text ) {


                if ($this->_getName($text) == 'up[2011-12-01][31256][departure]') {
                    print $text;
                    exit();
                }

                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($text)] = array(
                    'type'	=> 'text',
                    'value'	=> $this->_getValue($text),
                    'name'	=> $this->_getName($text),
                    'status'=> $this->_getStatus($text),
                    'onblur'=> $this->_getOnblur($text)
                );
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?hidden[\"']? ?[^>]*>/isU", $form, $hiddens) ) {
            foreach ( $hiddens[0] as $hidden ) {
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($hidden)] = array(
                    'type'	=> 'hidden',
                    'value'	=> $this->_getValue($hidden),
                    'name'	=> $this->_getName($hidden),
                    'status'=> $this->_getStatus($hidden),
                    'onblur'=> $this->_getOnblur($hidden)
                );
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?password[\"']? ?[^>]*>/iU", $form, $passwords) ) {
            foreach ( $passwords[0] as $password ) {
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($password)] = array(
                    'type'	=> 'password',
                    'value'	=> $this->_getValue($password),
                    'name'	=> $this->_getName($password),
                    'status'=> $this->_getStatus($password),
                    'onblur'=> $this->_getOnblur($password)
                );
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?file[\"']? ?[^>]*>/iU", $form, $images) ) {
            foreach ( $images[0] as $image ) {
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($image)] = array(
                    'type'	=> 'file',
                    'name'	=> $this->_getName($image),
                    'value'	=> $this->_getValue($image),
                    'status'=> $this->_getStatus($image)
                );
            }
        }

        if ( preg_match_all("/<textarea[^>]*>.*<\/textarea>/isU", $form, $textareas) ) {
            foreach ( $textareas[0] as $textarea ) {
                preg_match("/<textarea[^>]*>(.*)<\/textarea>/isU", $textarea, $textarea_value);
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($textarea)] = array(
                    'type'	=> 'textarea',
                    'value'	=> $textarea_value[1],
                    'name'	=> $this->_getName($textarea),
                    'status'=> $this->_getStatus($textarea)
                );
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?checkbox[\"']?[^>]*>/isU", $form, $checkboxes) ) {


            foreach ( $checkboxes[0] as $checkbox ) {

                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($checkbox)]['type']='checkbox';
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($checkbox)]['name']=$this->_getName($checkbox);
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($checkbox)]['status']=$this->_getStatus($checkbox);

                if ( preg_match("/checked/i", $checkbox) ) {
                    $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($checkbox)]['value'] = $this->_getValue($checkbox,true);
                } else {
                    $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($checkbox)]['UnCheckedValue'] = $this->_getValue($checkbox);
                }
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?radio[\"']? ?[^>]*>/iU", $form, $radios) ) {
            foreach ( $radios[0] as $radio ) {
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($radio)]['type']='radio';
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($radio)]['name']=$this->_getName($radio);
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($radio)]['status']=$this->_getStatus($radio);
                if ( preg_match("/checked/i", $radio) ) {
                    $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($radio)]["value"] = $this->_getValue($radio);
                } else {
                    $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($radio)]['UnCheckedValue'][] = $this->_getValue($radio);
                }
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?submit[\"']? ?[^>]*>/iU", $form, $submits) ) {
            if ($GetSubmit) {
                foreach ( $submits[0] as $submit ) {
                    $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($submit)] = array(
                        'type'	=> 'submit',
                        'name'	=> $this->_getName($submit),
                        'value'	=> $this->_getValue($submit),
                        'status' =>$this->_getStatus($submit)
                    );
                    $this->button_counter++;
                }
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?button[\"']? ?[^>]*>/iU", $form, $buttons) ) {

            if ($GetButtons) {
                foreach ( $buttons[0] as $button ) {
                    $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($button)] = array(
                        'type'	=> 'button',
                        'name'	=> $this->_getName($button),
                        'value'	=> $this->_getValue($button),
                        'status' =>$this->_getStatus($button)
                    );
                    $this->button_counter++;
                }
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?reset[\"']? ?[^>]*>/iU", $form, $resets) ) {
            foreach ( $resets[0] as $reset ) {
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($reset)] = array(
                    'type'	=> 'reset',
                    'name'	=> $this->_getName($reset),
                    'value'	=> $this->_getValue($reset),
                    'status' =>$this->_getStatus($reset)
                );
                $this->button_counter++;
            }
        }

        if ( preg_match_all("/<input[^>]*type=[\"']?image[\"']? ?[^>]*>/iU", $form, $images) ) {
            foreach ( $images[0] as $image ) {
                $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($image)] = array(
                    'type'	=> 'reset',
                    'name'	=> $this->_getName($image),
                    'value'	=> $this->_getValue($image),
                    'status' =>$this->_getStatus($image)
                );
                $this->button_counter++;
            }
        }

        if ( preg_match_all("/<input[^>]*>/iU", $form, $texts) ) {
            foreach ( $texts[0] as $text ) {

                if ($this->_getType($text) == "") {
                    if ($this->_getName($text) == 'up[2011-12-01][31256][departure]') {
                        print $text;
                        print "<hr>";
                        print $this->_getType($text);
                        exit("None");
                    }

                    $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($text)] = array(
                        'type'	=> 'text',
                        'value'	=> $this->_getValue($text),
                        'name'	=> $this->_getName($text),
                        'status' =>$this->_getStatus($text),
                        'onblur'=> $this->_getOnblur($text)
                    );
                }
            }
        }
        if ( preg_match_all("/<select[^>]*>.+<\/select>/isU", $form, $selects) ) {
            foreach ( $selects[0] as $select ) {
                if ( preg_match("/multiple/i", $select) ) {
                    if ( preg_match_all("/<option[^>]*>.+<\/option>/isU", $select, $all_options) ) {
                        foreach ( $all_options[0] as $option ) {
                            if ( preg_match("/selected/i", $option) ) {
                                if ( preg_match("/value\s/iU", $option) ) {
                                    $option_value = $this->_getValue($option);
                                    $found_selected = 1;
                                } else {
                                    preg_match("/<option[^>]*>(.*)<\/option>/isU", $option, $option_value);
                                    $option_value = $option_value[1];
                                    $found_selected = 1;
                                }
                            }
                        }
                        if ( !isset($found_selected) ) {
                            if ( preg_match("/value/iU", $all_options[0][0]) ) {
                                $option_value = $this->_getValue($all_options[0][0]);
                            } else {
                                preg_match("/<option>(.*)<\/option>/iU", $all_options[0][0], $option_value);
                                $option_value = $option_value[1];
                            }
                        } else {
                            unset($found_selected);
                        }
                        $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($select)] = array(
                            'type'	=> 'mselect',
                            'value'	=> trim($option_value),
                            'name'	=> $this->_getName($select),
                            'status' =>$this->_getStatus($select)
                        );
                    }
                } else {
                    if (preg_match_all("/<option[^>]*>.+<\/option>/isU", $select, $all_options)) {
                        foreach ( $all_options[0] as $option ) {
                            if ( preg_match("/selected/i", $option) ) {
                                if ( preg_match("/value/iU", $option) ) {
                                    $option_value = $this->_getValue($option);
                                    $found_selected = 1;
                                } else {
                                    preg_match("/<option.*>(.*)<\/option>/isU", $option, $option_value);
                                    $option_value = $option_value[1];
                                    $found_selected = 1;
                                }
                            }
                        }

                        if ( !isset($found_selected) ) {
                            if (preg_match("/value/i", $all_options[0][0])) {
                                $option_value = $this->_getValue($all_options[0][0]);
                            } else {
                                preg_match("/<option>(.*)<\/option>/iU", $all_options[0][0], $option_value);
                                $option_value = $option_value[1];
                            }
                        } else {
                            unset($found_selected);
                        }

                        $this->_HtmlFroms[$this->_counter]['form_elemets'][$this->_getName($select)] = array(
                            'type'	=> 'select',
                            'value'	=> trim($option_value),
                            'name'	=> $this->_getName($select),
                            'status' =>$this->_getStatus($select)
                        );
                    }
                }
            }
        }
    }

    public function parseForms($html_data,$GetButtons = true,$GetSubmit = true) {

        $this->_HtmlFroms = array();
        $this->_counter = 0;

        $this->prepareHtmlData($html_data);

        if (!preg_match_all("/<form.*>.+<\/form>/isU", $this->html_data, $forms)) {
            preg_match_all("/<form.*>.+<\/form>/ism", $this->html_data, $forms);
        }
        if (count($forms[0]) > 0 ) {
            foreach ( $forms[0] as $form ) {
                preg_match("/<form.*name=[\"']?([\w\s]*)[\"']?[\s>]/i", $form, $form_name);
                $this->_HtmlFroms[$this->_counter]['form_data']['name'] = preg_replace("/[\"'<>]/", "", $form_name[1]);
                preg_match("/<form.*action=(\"([^\"]*)\"|'([^']*)'|[^>\s]*)([^>]*)?>/is", $form, $action);
                $this->_HtmlFroms[$this->_counter]['form_data']['action'] = preg_replace("/[\"'<>]/", "", $action[1]);
                preg_match("/<form.*method=[\"']?([\w\s]*)[\"']?[\s>]/i", $form, $method);
                $this->_HtmlFroms[$this->_counter]['form_data']['method'] = preg_replace("/[\"'<>]/", "", $method[1]);
                preg_match("/<form.*enctype=(\"([^\"]*)\"|'([^']*)'|[^>\s]*)([^>]*)?>/is", $form, $enctype);
                $this->_HtmlFroms[$this->_counter]['form_data']['enctype'] = preg_replace("/[\"'<>]/", "", $enctype[1]);

                $this->getElementsData($form,$GetButtons,$GetSubmit);
                $this->_counter++;
            }
        }
        return $this->_HtlmForms;
    }

    public function parseFormsByOrder($html_data,$GetButtons = true,$GetSubmit = true) {

        $this->_HtmlFroms = array();
        $this->_counter = 0;

        $this->prepareHtmlData($html_data);

        if (!preg_match_all("/<form.*>.+<\/form>/isU", $this->html_data, $forms)) {
            preg_match_all("/<form.*>.+<\/form>/ism", $this->html_data, $forms);
        }
        if (count($forms[0]) > 0 ) {
            foreach ( $forms[0] as $form ) {
                preg_match("/<form.*name=[\"']?([\w\s]*)[\"']?[\s>]/i", $form, $form_name);
                $this->_HtmlFroms[$this->_counter]['form_data']['name'] = preg_replace("/[\"'<>]/", "", $form_name[1]);
                preg_match("/<form.*action=(\"([^\"]*)\"|'([^']*)'|[^>\s]*)([^>]*)?>/is", $form, $action);
                $this->_HtmlFroms[$this->_counter]['form_data']['action'] = preg_replace("/[\"'<>]/", "", $action[1]);
                preg_match("/<form.*method=[\"']?([\w\s]*)[\"']?[\s>]/i", $form, $method);
                $this->_HtmlFroms[$this->_counter]['form_data']['method'] = preg_replace("/[\"'<>]/", "", $method[1]);
                preg_match("/<form.*enctype=(\"([^\"]*)\"|'([^']*)'|[^>\s]*)([^>]*)?>/is", $form, $enctype);
                $this->_HtmlFroms[$this->_counter]['form_data']['enctype'] = preg_replace("/[\"'<>]/", "", $enctype[1]);

                $form = preg_replace("/[\r\n]/","",$form);
                $form = preg_replace("/<(input|select)/is","|HdnBreak|<$1",$form);

                $FormLines = explode("|HdnBreak|",$form);


                foreach ($FormLines as $line=>$Data) {
                    $this->getElementsData($Data,$GetButtons,$GetSubmit);
                }

                $this->_counter++;
            }
        }
        return $this->_HtlmForms;
    }

    private function _getName( $string ) {
        preg_match("/name=(.)/",$string,$limiter);

        switch ($limiter[1]) {
            case "'":
                preg_match("/name=[']([^']+)[']+[^>]*>/is", $string, $match);
                break;
            case "\"" :
                preg_match("/name=[\"]([^\"]+)[\"]+[^>]*>/is", $string, $match);
                break;
            default :
                preg_match("/name=([^ ]+) [^>]*>/is", $string, $match);
                break;
        }

        if ($match[1] != "" ) {
            $val_match = preg_replace("/[\"']*/", "", trim($match[1]));
            unset($string);
            $val_match=str_replace('[]','',$val_match);
            return trim($val_match);
        }
    }

    private function _getStatus($string) {
        if (preg_match("/disabled/i", $string) ) {
            $val_match = false;
        }
        else {
            $val_match = true;
        }
        return $val_match;
    }

    private function _getOnblur($string) {

        preg_match("/onblur=(.)/i",$string,$limiter);

        switch ($limiter[1]) {
            case "'":
                preg_match("/onblur=[']([^']+)[']+[^>]*>/is", $string, $match);
                break;
            case "\"" :
                preg_match("/onblur=[\"]([^\"]+)[\"]+[^>]*>/is", $string, $match);
                break;
            default :
                preg_match("/onblur=([^ ]+) [^>]*>/is", $string, $match);
                break;
        }

        if ($match[1] != "" ) {
            $val_match = preg_replace("/[\"']*/", "", trim($match[1]));
            unset($string);
            $val_match=str_replace('[]','',$val_match);
            return trim($val_match);
        }
    }

    private function _getType( $string ) {

        preg_match("/type=(.)/i",$string,$limiter);

        switch ($limiter[1]) {
            case "'":
                preg_match("/type=[']([^']+)[']+[^>]*>/is", $string, $match);
                break;
            case "\"" :
                preg_match("/type=[\"]([^\"]+)[\"]+[^>]*>/is", $string, $match);
                break;
            default :
                preg_match("/type=([^ ]+) [^>]*>/is", $string, $match);
                break;
        }

        $val_match = preg_replace("/[\"']*/", "", trim($match[1]));
        unset($string);
        $val_match=str_replace('[]','',$val_match);

        return trim($val_match);
    }

    private function _getValue( $string ,$Checked = false) {

        preg_match("/value=(.)/i",$string,$limiter);

        switch ($limiter[1]) {
            case "'":
                preg_match("/value=[']([^']+)[']+[^>]*>/is", $string, $match);
                break;
            case "\"" :
                preg_match("/value=[\"]([^\"]+)[\"]+[^>]*>/is", $string, $match);
                break;
            default :
                preg_match("/value=([^ ]+) [^>]*>/is", $string, $match);
                break;
        }

        if ($match[1] != "" ) {
            $val_match = preg_replace("/[\"']*/", "", trim($match[1]));
            unset($string);
            $val_match=str_replace('[]','',$val_match);
        }
        else {
            if ($Checked) {
                $val_match = "on";
            }
        }

        return trim($val_match);
    }

    public function parseCookies($html_data,$ReturnCurrentValue = false) {
        $this->prepareHtmlData($html_data);
        if (!$ReturnCurrentValue) {
            $CookieList = $this->_HttpCookies;
        }

        if ($this->html_data != null){
            preg_match_all("/document\.cookie = '([^']+)';/",$this->html_data,$out);
            $DocumentVariables = $out[1][0];
            $Cook = explode(";",$DocumentVariables);
            foreach ($Cook as $value) {
                $v = explode("=",$value);
                $CookieList[trim($v[0])] = trim($v[1]);
            }
            preg_match_all("/Set-Cookie: (.*);/im",$this->html_data,$out);

            if (is_array($out[1])) {
                foreach ($out[1] as $ck=>$cv) {

                    $HeaderVariables = $cv;

                    $Cook = explode(";",$HeaderVariables);
                    $cookieval = null;
                    foreach ($Cook as $value) {
                        $v = explode("=",$value);
                        for ($i =1 ;$i<count($v);$i++) {
                            $cookieval .= ($i ==1 ? trim($v[$i]) : "=".trim($v[$i]));
                        }
                        $CookieList[trim($v[0])] = $cookieval;
                        unset($cookieval);
                    }
                }
            }


            unset($CookieList["path"],$CookieList["Domain"],$CookieList["domain"],$CookieList["expires"],$CookieList[""]);
            if ($ReturnCurrentValue) {
                return $CookieList;
            }
            else {
                return $this->_HttpCookies = $CookieList;
            }

        }
    }
}