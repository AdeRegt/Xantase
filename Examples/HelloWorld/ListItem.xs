function build with rootdoc data params
create div node called lid
create h1 node called uname on lid
set property style.backgroundColor of uname to "green"
create string variable called liw 
set value of liw from call concat of liw with params.id "# " params.name
set property innerHTML of uname to liw
end function