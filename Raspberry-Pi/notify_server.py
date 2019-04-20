# import
from datetime import datetime
import global_var
import requests

def notification_for_thresholds(battery_voltage_value, battery_current_value,
                   solar_panel_voltage_value, solar_panel_current_value,
                   charge_controller_current_value,
                   temperature_inner_value, temperature_outer_value,
                   humidity_inner_value, humidity_outer_value,
                   threshold_battery_voltage_lower, threshold_battery_voltage_upper,
                   threshold_battery_current_lower, threshold_battery_current_upper,
                   threshold_solar_panel_voltage_lower, threshold_solar_panel_voltage_upper,
                   threshold_solar_panel_current_lower, threshold_solar_panel_current_upper,
                   threshold_charge_controller_current_lower, threshold_charge_controller_current_upper,
                   threshold_temperature_inner_lower, threshold_temperature_inner_upper,
                   threshold_temperature_outer_lower, threshold_temperature_outer_upper,
                   threshold_humidity_inner_lower, threshold_humidity_outer_lower,
                   threshold_humidity_inner_upper, threshold_humidity_outer_upper):
    try:
        current_hour = int(datetime.now().strftime("%H")) # Uses military hours (0-23)
        if current_hour >= 9 and current_hour <= 16:
            print("Contacting server for threshold notification...")            
            
            if battery_voltage_value <= threshold_battery_voltage_lower or battery_voltage_value >= threshold_battery_voltage_upper:
                global_var.PIPAYLOAD["noti"] = "bvoltage"
                global_var.PIPAYLOAD["valu"] = battery_voltage_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if battery_current_value <= threshold_battery_current_lower or battery_current_value >= threshold_battery_current_upper:
                global_var.PIPAYLOAD["noti"] = "bcurrent"
                global_var.PIPAYLOAD["valu"] = battery_current_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if solar_panel_voltage_value <= threshold_solar_panel_voltage_lower or solar_panel_voltage_value >= threshold_solar_panel_voltage_upper:
                global_var.PIPAYLOAD["noti"] = "spvoltage"
                global_var.PIPAYLOAD["valu"] = solar_panel_voltage_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if solar_panel_current_value <= threshold_solar_panel_current_lower or solar_panel_current_value >= threshold_solar_panel_current_upper:
                global_var.PIPAYLOAD["noti"] = "spcurrent"
                global_var.PIPAYLOAD["valu"] = solar_panel_current_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if charge_controller_current_value <= threshold_charge_controller_current_lower or charge_controller_current_value >= threshold_charge_controller_current_upper:
                global_var.PIPAYLOAD["noti"] = "cccurrent"
                global_var.PIPAYLOAD["valu"] = charge_controller_current_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if temperature_inner_value is not None and temperature_inner_value <= threshold_temperature_inner_lower or temperature_inner_value >= threshold_temperature_inner_upper:
                global_var.PIPAYLOAD["noti"] = "temperatureI"
                global_var.PIPAYLOAD["valu"] = temperature_inner_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if temperature_outer_value is not None and temperature_outer_value <= threshold_temperature_outer_lower or temperature_outer_value >= threshold_temperature_outer_upper: 
                global_var.PIPAYLOAD["noti"] = "temperatureO"
                global_var.PIPAYLOAD["valu"] = temperature_outer_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if humidity_inner_value is not None and humidity_inner_value <= threshold_humidity_inner_lower or humidity_inner_value >= threshold_humidity_inner_upper:
                global_var.PIPAYLOAD["noti"] = "humidityI"
                global_var.PIPAYLOAD["valu"] = humidity_inner_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
            if humidity_outer_value is not None and humidity_outer_value <= threshold_humidity_outer_lower or humidity_outer_value >= threshold_humidity_outer_upper:
                global_var.PIPAYLOAD["noti"] = "humidityO"
                global_var.PIPAYLOAD["valu"] = humidity_outer_value
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD)
                # print(serverConfirmation.text.strip())
                global_var.PIPAYLOAD.pop("noti")
                global_var.PIPAYLOAD.pop("valu")
    except Exception as e: pass #print(e)  # Unable to connect to internet, so just disregard sending a notification
# end

def notification_for_motion():
    print("Contacting server for motion notification...")
    try:        
        global_var.PIPAYLOAD["noti"] = "motion"
        server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=global_var.PIPAYLOAD, timeout=1)
        # print(serverConfirmation.text.strip())
        global_var.PIPAYLOAD.pop("noti")
    except Exception as e: pass #print(e) # Unable to connect to internet, so just disregard sending a notification
# end