function build with rootdoc data params
create h1 node called h1elem
create string variable called gingerbread and set value to "This is an " & "example" & " of xantase!"
set property innerHTML of h1elem to gingerbread
foreach params as pew for spawn ListItem on rootdoc using pew
create string variable called tmp
set value of tmp from call at of params with 0
if id of tmp equals 5 else js window.alert('ok')
end function