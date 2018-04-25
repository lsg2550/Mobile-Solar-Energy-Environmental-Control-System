<?php
//This will only be called for the vitals that are controllable (overwritable) - e.g Solar Panel, Exhaust
function generateSelectOptions($vitalname, $currentstatus, $rpid) {
    $selectHTML = '<th>' . $vitalname . '</th>';
    
    $secondOption = '';
    switch($vitalname) {
        case "Solar Panel":
            if($currentstatus === 'charging'){
                $secondOption = 'not charging';
            } else {
                $secondOption = 'charging';
            }
            break;
        case "Exhaust":
            if($currentstatus === 'on'){
                $secondOption = 'off';
            } else {
                $secondOption = 'on';
            }
            break;
    }

    $selectHTML .= '<th><select>'; //Second Column - Status Column
    $selectHTML .= '<option name="currentstatus[]" value="' . $currentstatus . '">' . $currentstatus . '</option>';
    $selectHTML .= '<option name="secondOption[]" value="' . $secondOption . '"> ' . $secondOption . ' </option>';
    $selectHTML .= '</select></th>';
    $selectHTML .= '<input type="hidden" name="vitalname[]" value="' . $vitalname . '">';
    $selectHTML .= '<input type="hidden" name="rpid[]" value="' . $rpid . '">';
    return $selectHTML;
}

//This will only be called for the vitals that are not controllable (overwritable), but whose thresholds are - e.g Battery, Temperature, Photo
function generateThresholdOptions($vitalname, $vitallower, $vitalupper, $rpid) {
    $selectHTML = '';
    
    //Populate threshold row START
    $selectHTML .= '<th>' . $vitalname . '</th>';
    $selectHTML .= '<th>'; //Second Column - VLower Column
    $selectHTML .= '<input type="text" name="vitallower[]" value="' . $vitallower . '">';
    $selectHTML .= '</th>';
    $selectHTML .= '<th>'; //Third Column - VUpper Column
    $selectHTML .= '<input type="text" name="vitalupper[]" value="' . $vitalupper . '">';
    $selectHTML .= '</th>';
    $selectHTML .= '<input type="hidden" name="vitalname[]" value="' . $vitalname . '">';
    $selectHTML .= '<input type="hidden" name="rpid[]" value="' . $rpid . '">';
    //Populate threshold row END
    
    return $selectHTML;
} 
?>