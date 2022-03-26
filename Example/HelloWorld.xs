function build with rootdoc data params
create h1 node called h1elem
create string variable called gingerbread and set value to "helloworld"
set property innerHTML of h1elem to gingerbread
call appendChild of rootdoc with gingerbread 
create HelloWorld variable called ins
if ewe of params is 1 then call build of ins with rootdoc data
end function