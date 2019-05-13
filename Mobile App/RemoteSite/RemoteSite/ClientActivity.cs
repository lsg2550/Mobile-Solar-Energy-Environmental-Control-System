using Android.App;
using Android.OS;
using Android.Widget;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Net.Http;

namespace RemoteSite {

    [Activity(Label = "ClientActivity")]
    public class ClientActivity : Activity {
        protected override void OnCreate(Bundle savedInstanceState) {
            base.OnCreate(savedInstanceState);
            SetContentView(Resource.Layout.Client);

            //Logout Button
            Button buttonLogout = FindViewById<Button>(Resource.Id.buttonLogout);
            buttonLogout.Click += delegate {
                OnClickLogoutButton();
            };

            //Fill Expandable List View
            FillExpandableListAsync();
        }

        public void OnClickLogoutButton() {
            MyClient.GetInstance().ClearClientInfo();
            Finish();
        }

        public async void FillExpandableListAsync() {
            // Payload
            List<KeyValuePair<string, string>> payload = new List<KeyValuePair<string, string>> {
                new KeyValuePair<string, string>("username", MyClient.GetInstance().User),
                new KeyValuePair<string, string>("password", MyClient.GetInstance().Pass)
            };

            // Send request to server if it is available
            HttpContent content;
            HttpResponseMessage response;
            string responseString = "";
            try {
                content = new FormUrlEncodedContent(payload);
                response = await HttpConnector.client.PostAsync("http://remote-ecs.000webhostapp.com/index_files/androidclientconfirm.php", content);
                responseString = await response.Content.ReadAsStringAsync();
                responseString = Parse.ReplaceWhiteSpace(responseString);
                Console.Write(responseString);
            } catch (HttpRequestException e) {
                Toast.MakeText(this, e.Message, ToastLength.Long).Show();
                return;
            }

            // Check Response
            if (responseString == HttpConnector.ResponseCodes.NO.ToString()) {
                Toast.MakeText(this, "Error received.\nPlease try again.", ToastLength.Long).Show();
                return;
            }

            //Get from JSON on server
            HttpResponseMessage responseJSONCS, responseJSONL;
            string responseStringJSONCS = "", responseStringJSONL = "";
            try {
                responseJSONCS = await HttpConnector.client.GetAsync("http://remote-ecs.000webhostapp.com/index_files/androidgetcs.php?username=" + MyClient.GetInstance().User);
                responseJSONL = await HttpConnector.client.GetAsync("http://remote-ecs.000webhostapp.com/index_files/androidgetl.php?username=" + MyClient.GetInstance().User);
                responseStringJSONCS = await responseJSONCS.Content.ReadAsStringAsync();
                responseStringJSONL = await responseJSONL.Content.ReadAsStringAsync();
                responseStringJSONCS = Parse.ReplaceWhiteSpace(responseStringJSONCS);
                responseStringJSONL = Parse.ReplaceWhiteSpace(responseStringJSONL);
            } catch (HttpRequestException e) {
                Toast.MakeText(this, e.Message, ToastLength.Long).Show();
                return;
            }

            List<RPiCurrentStatus> currentStatus = JsonConvert.DeserializeObject<List<RPiCurrentStatus>>(responseStringJSONCS);
            List<RPiLog> log = JsonConvert.DeserializeObject<List<RPiLog>>(responseStringJSONL);

            // Set List and Adapter
            ExpandableListView expandableListViewLog = FindViewById<ExpandableListView>(Resource.Id.expandableListViewLog);
            ExpandableListViewAdapter expandableListViewAdapterLog = new ExpandableListViewAdapter(currentStatus, log, this);
            expandableListViewLog.SetAdapter(expandableListViewAdapterLog);
        }
    }
}