PACKNAME="oneighty"
VERSION="0.98c"
ln -s . $PACKNAME
zip  -x "$PACKNAME/$PACKNAME" "$PACKNAME/$PACKNAME/*" "$PACKNAME/.git*" "$PACKNAME/classes/xmlrpc/test*" "$PACKNAME/classes/xmlrpc/demo*" "$PACKNAME/classes/xmlrpc/doc*" "$PACKNAME/writecrowd_oneighty.zip" "$PACKNAME/pack.sh" -r writecrowd_oneighty_$VERSION.zip $PACKNAME
rm $PACKNAME
