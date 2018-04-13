<?php

//This will only be called for the vitals that are contrallable (overwritable) - e.g Solar Panel, Exhaust
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
    $selectHTML .= '</select> <input type="hidden" name="rpid[]" value="' . $rpid . '"> </th>';
    return $selectHTML;
}

//This will only be called for the vitals that are not contrallable (overwritable), but whose thresholds are - e.g Battery, Temperature, Photo
function generateThresholdOptions($vitalname, $vitallower, $vitalupper, $rpid) {
    $selectHTML = '<th>' . $vitalname . '</th>';
    $selectHTML .= '<th>'; //Second Column - VLower Column
    $selectHTML .= '<input type="text" name="vitallower[]" value="' . $vitallower . '">';
    $selectHTML .= '</th>';
    $selectHTML .= '<th>'; //Third Column - VUpper Column
    $selectHTML .= '<input type="text" name="vitalupper[]" value="' . $vitalupper . '">';
    $selectHTML .= '</th>';
    $selectHTML .= '<input type="hidden" name="rpid[]" value="' . $rpid . '">';
    return $selectHTML;
} 

?>