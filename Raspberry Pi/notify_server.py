from datetime import datetime
import math_calc as MC
import requests

# RaspberryPi Identification Number (rpid) & Payload for Server Confirmation
rpid = 0
pipayload = {"rpid": rpid}

def CheckAndNotify(battery_voltage_value, battery_current_value,
                   solar_panel_voltage_value, solar_panel_current_value,
                   charge_controller_current_value,
                   temperature_inner_value, temperature_outer_value,
                   threshold_battery_voltage_lower, threshold_battery_voltage_upper,
                   threshold_battery_current_lower, threshold_battery_current_upper,
                   threshold_solar_panel_voltage_lower, threshold_solar_panel_voltage_upper,
                   threshold_solar_panel_current_lower, threshold_solar_panel_current_upper,
                   threshold_charge_controller_current_lower, threshold_charge_controller_current_upper,
                   threshold_temperature_inner_lower, threshold_temperature_inner_upper,
                   threshold_temperature_outer_lower, threshold_temperature_outer_upper):
    try:
        current_hour = int(datetime.now().strftime("%H")) # Uses military hours (0-23)
        if current_hour >= 9 and current_hour <= 16:
            if battery_voltage_value <= threshold_battery_voltage_lower or battery_voltage_value >= threshold_battery_voltage_upper:
                pipayload["noti"] = "bvoltage"
                pipayload["valu"] = battery_voltage_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                pipayload.pop("valu")
            if battery_current_value <= threshold_battery_current_lower or battery_current_value >= threshold_battery_current_upper:
                pipayload["noti"] = "bcurrent"
                pipayload["valu"] = battery_current_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                pipayload.pop("valu")
            if solar_panel_voltage_value <= threshold_solar_panel_voltage_lower or solar_panel_voltage_value >= threshold_solar_panel_voltage_upper:
                pipayload["noti"] = "spvoltage"
                pipayload["valu"] = solar_panel_voltage_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                pipayload.pop("valu")
            if solar_panel_current_value <= threshold_solar_panel_current_lower or solar_panel_current_value >= threshold_solar_panel_current_upper:
                pipayload["noti"] = "spcurrent"
                pipayload["valu"] = solar_panel_current_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                pipayload.pop("valu")
            if charge_controller_current_value <= threshold_charge_controller_current_lower or charge_controller_current_value >= threshold_charge_controller_current_upper:
                pipayload["noti"] = "cccurrent"
                pipayload["valu"] = charge_controller_current_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                pipayload.pop("valu")
            if temperature_inner_value is not None:
                if temperature_inner_value <= threshold_temperature_inner_lower or temperature_inner_value >= threshold_temperature_inner_upper:
                    pipayload["noti"] = "temperatureI"
                    pipayload["valu"] = temperature_inner_value
                    server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                    # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                pipayload.pop("valu")
            if temperature_outer_value is not None:                
                if temperature_outer_value <= threshold_temperature_outer_lower or temperature_outer_value >= threshold_temperature_outer_upper: 
                    pipayload["noti"] = "temperatureO"
                    pipayload["valu"] = temperature_outer_value
                    server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                    # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                pipayload.pop("valu")
    except Exception as e: print(e)  # Unable to connect to internet, so just disregard sending a notification
# CheckAndNotify end