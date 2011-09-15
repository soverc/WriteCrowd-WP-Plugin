PACKNAME="oneighty"
ln -s . $PACKNAME
zip  -x "$PACKNAME/$PACKNAME" "$PACKNAME/$PACKNAME*" "$PACKNAME/.git*" "$PACKNAME/classes/xmlrpc/test*" "$PACKNAME/classes/xmlrpc/demo*" "$PACKNAME/classes/xmlrpc/doc*" "$PACKNAME/writecrowd_oneighty.zip" "$PACKNAME/pack.sh" -r writecrowd_oneighty.zip $PACKNAME
rm $PACKNAME
