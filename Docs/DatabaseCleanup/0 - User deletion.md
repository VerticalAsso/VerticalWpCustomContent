


# Programmatically remove users and all their data :
```sh
curl --request POST \
  --url 'http://localhost:8080/wp-admin/users.php?s=&_wpnonce=9e737b023b&_wp_http_referer=%2Fwp-admin%2Fusers.php%3Fs%26action%3D-1%26new_role%26ure_add_role%26ure_revoke_role%26primary_role%3Dum_adherent%26_um_wpnonce%3D0a69759c27%26um_user_status%3Dinactive%26um_filter_users%3DFiltrer%26paged%3D1%26action2%3D-1%26new_role2%26ure_add_role_2%26ure_revoke_role_2&action=delete&bulk_action=Appliquer&new_role=&ure_add_role=&ure_revoke_role=&primary_role=um_adherent&um_user_status=inactive&paged=1&users%5B0%5D=1751&action2=delete&new_role2=&ure_add_role_2=&ure_revoke_role_2=' \
  --header 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' \
  --header 'Accept-Encoding: gzip, deflate, br, zstd' \
  --header 'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3' \
  --header 'Connection: keep-alive' \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --header 'Cookie: wordpress_37d007a56d816107ce5b52c10342db37=Vertical%7C1745576695%7C2xipNhOoeZt2Lch5oxass3zDKuzPm8Xf7La3F6vCC9u%7C81cc4effebe0c4b0125b328ac9d202f05e83e038bea2e4ce371fef03f60279ef; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dtinymce%26post_dfw%3Doff%26hidetb%3D1%26advImgDetails%3Dshow%26editor_plain_text_paste_warning%3D1%26posts_list_mode%3Dlist%26mfold%3Do; wp-settings-time-1=1745393126; of_current_opt=%23of-option-header; PHPSESSID=fb8f0dba2052f1218bd666468902a3e9; wordpress_test_cookie=WP%20Cookie%20check; wp_lang=fr_FR; wordpress_logged_in_37d007a56d816107ce5b52c10342db37=Vertical%7C1745576695%7C2xipNhOoeZt2Lch5oxass3zDKuzPm8Xf7La3F6vCC9u%7Cd6596dbae553a7305fbe3d893deecbdcc11d15f0afebf40b32600240a8ea62bc' \
  --header 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:137.0) Gecko/20100101 Firefox/137.0' \
  --data _wpnonce=1db05abe13 \
  --data 'users[]=1751' \
  --data delete_option=delete \
  --data action=dodelete \
  --data 'submit=Confirmer+cette+action'
```