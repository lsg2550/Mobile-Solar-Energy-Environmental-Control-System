using System.Net.Http;

namespace RemoteSite {
    public class MyClient {
        private static MyClient instance;
        private string user;
        private string pass;

        private MyClient() { }

        public static MyClient GetInstance() {
            if (instance == null) {
                instance = new MyClient();
            }

            return instance;
        }

        public string User { get; set; }

        public string Pass { get; set; }
    }
}