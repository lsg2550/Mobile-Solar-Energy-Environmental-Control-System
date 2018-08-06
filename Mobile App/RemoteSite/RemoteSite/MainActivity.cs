using Android.App;
using Android.Content;
using Android.OS;
using Android.Widget;
using System.Collections.Generic;
using System.Net.Http;

namespace RemoteSite {

    [Activity(Label = "RemoteSite", MainLauncher = true)]
    public class MainActivity : Activity {
        protected override void OnCreate(Bundle bundle) {
            base.OnCreate(bundle);
            SetContentView(Resource.Layout.Main);

            //Sign-In Button
            Button buttonSignIn = FindViewById<Button>(Resource.Id.buttonSignIn);
            buttonSignIn.Click += delegate {
                OnClickSignInButton();
            };
        }

        public async void OnClickSignInButton() {
            //Get EditText
            EditText username = FindViewById<EditText>(Resource.Id.textField_Username);
            EditText password = FindViewById<EditText>(Resource.Id.textField_Password);
            string strUsername = username.Text;
            string strPassword = password.Text;

            if (strUsername == "" || strPassword == "") {
                Toast.MakeText(this, "Please fill the fields!", ToastLength.Long).Show();
                return;
            }

            //Payload
            List<KeyValuePair<string, string>> payload = new List<KeyValuePair<string, string>> {
                new KeyValuePair<string, string>("username", strUsername),
                new KeyValuePair<string, string>("password", strPassword)
            };

            //Send Request
            HttpContent content;
            HttpResponseMessage response;
            string responseString = "";
            try {
                content = new FormUrlEncodedContent(payload);
                response = await HttpConnector.client.PostAsync("http://remote-ecs.000webhostapp.com/index_files/androidloginconfirm.php", content);
                responseString = await response.Content.ReadAsStringAsync();
                responseString = Parse.ReplaceWhiteSpace(responseString);
            } catch (HttpRequestException e) {
                Toast.MakeText(this, e.Message, ToastLength.Long).Show();
                return;
            }

            //Process Response
            if (responseString == HttpConnector.ResponseCodes.OK.ToString()) {
                //Toast.MakeText(this, ResponseCodes.OK.ToString(), ToastLength.Short).Show();
                MyClient.GetInstance().User = strUsername;
                MyClient.GetInstance().Pass = strPassword;
                StartActivity(new Intent(this, typeof(ClientActivity))); //Switch to ClientActivity
            } else if (responseString == HttpConnector.ResponseCodes.NO.ToString()) {
                Toast.MakeText(this, "Incorrect Credentials!\nPlease Try Again!", ToastLength.Long).Show();
            }

            //Clear EditText
            username.Text = "";
            password.Text = "";
        }
    }
}