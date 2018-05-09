using Android;
using Android.App;
using Android.OS;

namespace RemoteSite {
    [Activity(Label = "RemoteSite", MainLauncher = true)]
    public class MainActivity : Activity {

        protected override void OnCreate(Bundle bundle) {
            base.OnCreate(bundle);
            SetContentView(Resource.Layout.Main);
        }

    }
}

