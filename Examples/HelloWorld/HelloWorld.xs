function build with rootdoc data params
create h1 node called h1elem
create string variable called gingerbread and set value to "This is an " & "example" & " of xantase!"
set property innerHTML of h1elem to gingerbread
foreach params as pew for spawn ListItem on rootdoc using pew
if params[0].id equals 5 else js window.alert('ok')
end function