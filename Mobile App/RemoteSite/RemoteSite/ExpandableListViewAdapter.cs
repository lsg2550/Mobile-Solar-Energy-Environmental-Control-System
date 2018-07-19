using Android.Content;
using Android.Views;
using Android.Widget;

namespace RemoteSite {

    class ExpandableListViewAdapter : BaseExpandableListAdapter {

        private string[] groupNames;
        private string[][] childNames;
        private Context context;

        public ExpandableListViewAdapter(string[] groupNames, string[][] childNames, Context context) {
            this.groupNames = groupNames;
            this.childNames = childNames;
            this.context = context;
        }

        public override int GroupCount => groupNames.Length;

        public override bool HasStableIds => false;

        public override Java.Lang.Object GetChild(int groupPosition, int childPosition) {
            return childNames[groupPosition][childPosition];
        }

        public override long GetChildId(int groupPosition, int childPosition) {
            return childPosition;
        }

        public override int GetChildrenCount(int groupPosition) {
            return childNames[groupPosition].Length;
        }

        public override View GetChildView(int groupPosition, int childPosition, bool isLastChild, View convertView, ViewGroup parent) {
            TextView textView = new TextView(context);
            textView.SetText(childNames[groupPosition][childPosition], TextView.BufferType.Normal);
            textView.SetTextSize(Android.Util.ComplexUnitType.Dip, 36);
            textView.Click += delegate {
                Toast.MakeText(context, textView.Text, ToastLength.Long).Show();
            };
            return textView;
        }

        public override Java.Lang.Object GetGroup(int groupPosition) {
            return groupNames[groupPosition];
        }

        public override long GetGroupId(int groupPosition) {
            return groupPosition;
        }

        public override View GetGroupView(int groupPosition, bool isExpanded, View convertView, ViewGroup parent) {
            TextView textView = new TextView(context);
            textView.SetText(groupNames[groupPosition], TextView.BufferType.Normal);
            textView.SetTextSize(Android.Util.ComplexUnitType.Dip, 36);
            return textView;
        }

        public override bool IsChildSelectable(int groupPosition, int childPosition) => false;
    }
}