namespace RemoteSite {
    public class MyClient {
        private static MyClient instance;

        private MyClient() { }
        public static MyClient GetInstance() {
            if (instance == null) { instance = new MyClient(); }
            return instance;
        }

        public string User { get; set; }
        public string Pass { get; set; }
        public void ClearClientInfo() { User = ""; Pass = ""; }
    }

    public class RPiCurrentStatus {
        public string RPID { get; set; }
        public string VN { get; set; }
        public string VV { get; set; }
        public string TS { get; set; }
        public string[] ToArray() {
            return new string[] { RPID, VN, VV, TS };
        }
    }

    public class RPiLog {
        public string RPID { get; set; }
        public string VID { get; set; }
        public string TYP { get; set; }
        public string V1 { get; set; }
        public string V2 { get; set; }
        public string TS { get; set; }
        public string[] ToArray() {
            return new string[] { RPID, VID, TYP, V1, V2, TS };
        }
    }
}