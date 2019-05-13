using Android.App;
using Android.Content;
using Android.OS;
using Android.Widget;
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Security.Cryptography;
using System.Text;

namespace RemoteSite {

    [Activity(Label = "RemoteSite", MainLauncher = true)]
    public class LoginActivity : Activity {
        protected override void OnCreate(Bundle bundle) {
            base.OnCreate(bundle);
            SetContentView(Resource.Layout.Main);

            // Sign-In Button
            Button buttonSignIn = FindViewById<Button>(Resource.Id.buttonSignIn);
            buttonSignIn.Click += delegate {
                OnClickSignInButton();
            };
        }

        protected async void OnClickSignInButton() {
            // Get Username and Password from EditText fields
            EditText username = FindViewById<EditText>(Resource.Id.textField_Username);
            EditText password = FindViewById<EditText>(Resource.Id.textField_Password);
            string strUsername = username.Text;
            string strPassword = ConvertStringToHashString(password.Text);

            // Debug (Comment out when not using it)
            Console.Write(strPassword);

            // Check if username and password fields are filled
            if (strUsername == "" || strPassword == "") {
                Toast.MakeText(this, "Please fill the fields!", ToastLength.Long).Show();
                return;
            }

            // Create payload object to send to CMS
            List<KeyValuePair<string, string>> payload = new List<KeyValuePair<string, string>> {
                new KeyValuePair<string, string>("username", strUsername),
                new KeyValuePair<string, string>("password", strPassword)
            };

            // Send Request
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

            // Process Response
            if (responseString == HttpConnector.ResponseCodes.OK.ToString()) {
                //Toast.MakeText(this, ResponseCodes.OK.ToString(), ToastLength.Short).Show();
                MyClient.GetInstance().User = strUsername;
                MyClient.GetInstance().Pass = strPassword;
                StartActivity(new Intent(this, typeof(ClientActivity))); //Switch to ClientActivity
            } else if (responseString == HttpConnector.ResponseCodes.NO.ToString()) {
                Toast.MakeText(this, "Incorrect Credentials!\nPlease Try Again!", ToastLength.Long).Show();
            }

            // Clear EditText fields
            username.Text = "";
            password.Text = "";
        }

        private string ConvertStringToHashString(string stringToHash) {
            // Initialize
            SHA1 sha1 = new SHA1CryptoServiceProvider();
            StringBuilder stringBuilder = new StringBuilder();
            byte[] stringToHash_ByteArr = Encoding.UTF8.GetBytes(stringToHash);
            byte[] stringToHash_Hash = sha1.ComputeHash(stringToHash_ByteArr);

            // Convert bytearray to string hash using StringBuilder
            foreach (byte hashChar in stringToHash_Hash) {
                stringBuilder.Append(hashChar.ToString("X2"));
            }

            // Return string hash
            return stringBuilder.ToString();
        }
    }
}