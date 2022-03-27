function build with rootdoc data params
create h1 node called h1elem
create string variable called gingerbread and set value to "This is an example of xantase!"
set property innerHTML of h1elem to gingerbread
call appendChild of rootdoc with h1elem 
foreach params as pew for spawn ListItem on rootdoc using pew
end function