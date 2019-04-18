  //custom js file
 
  $(document).ready(function(){
     //date picker
    var date_input=$('input[name="dateDebut"]'); //our date input has the name "date"
    var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
    var options={
      format: 'yyyy/mm/dd',
      container: container,
      todayHighlight: true,
      autoclose: true,
    };
    date_input.datepicker(options);
    // modal 
       $('#myModal').on('click', function () {
      console.log("test");
      var tmpid = $(document.activeElement).attr('id'); 
 
     $('#myInput').trigger('focus');
      })
  
  
  
   });

function  appendLi(list,text,type){
    var ul = document.getElementById(list);
    var li = document.createElement("li");
    li.appendChild(document.createTextNode(text));
    li.innerHTML=text+"<i class='fas fa-minus-circle text-danger' style='float: right;' onclick='deleteSettings(this)'></i>";
    
    li.setAttribute("id",type);
    li.setAttribute("class","list-group-item");
    ul.appendChild(li);
}   

function searchSettings(element) {
    console.log(element.parentElement.previousElementSibling.value);
    val = element.parentElement.previousElementSibling.value.trim(); // we fetch the value of sibling input
    if (val === ""){ // if this value is empty -> return
        console.log("Please input value");
        // add notify
        return;
    }
    switch (element.id) { // thanks to the ID, we're checking which input is concerned
        // then for each case we're adding their respected char (@ # or -), it will be then used in ./src/Controller/AjaxController.php
        case "sPseudo":
            keyword = "@" + val;
            break;
        case "sHash":
            keyword = "#" + val;
            break;
        case "sBlack":
            keyword = "-" + val;
            break;
        default:
            console.log("Error while processing keyword");
            // add notify
            break;
    }
    console.log(keyword);
    $.ajax({
        type:'POST',
        data:{'keyword':keyword},
        url:'/ajax/search_settings',
        complete:function(data,status){
            if(data.status !== 200){
                console.log(data);
                console.log('Not response 200');
                return;
            }
            //console.log('COMPLETE:')
            console.log(data.responseJSON.output);
            console.log(data.status);
            $.notify({
                    icon:'fa fa-check-circle',
                    title: "<strong>Success :</strong> ",
                    message:data.responseJSON.output // data below
                },
                {
                    type:'success',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
                appendLi(element.id+"UL",val,element.id);
        },
        error:function(jqXHR, textStatus) {
            //console.log('ERROR');
            //console.log(jqXHR.responseJSON);
            //console.log(jqXHR.responseJSON.status);
            $.notify({
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.responseJSON.output
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        }
    });
}

function deleteAccount(element){
    console.log(element.parentElement.innerText);
    val = element.parentElement.innerText.trim(); // we fetch the value of sibling input
    if (val === ""){ // if this value is empty -> return
        console.log("Please input value");
        // add notify
        return;
    }
    console.log(typeof (val));
    $.ajax({
        type:'DELETE',
        data:{'pseudo':val+''},
        url:'/ajax/acc',
        complete:function(data,status){
            if(data.status !== 200){
                console.log(data);
                console.log('Not response 200');
                return;
            }
            //console.log('COMPLETE:')
            console.log(data.responseJSON.output);
            console.log(data.status);
            $.notify({
                    icon:'fa fa-check-circle',
                    title: "<strong>Success :</strong> ",
                    message:data.responseJSON.output // data below
                },
                {
                    type:'success',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
            element.parentElement.parentElement.removeChild(element.parentElement);

        },
        error:function(jqXHR, textStatus) {
            console.log(jqXHR);
            $.notify({
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.responseJSON.output
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        }
    });
}

function deleteSettings(element){
    console.log(element.parentElement.innerText);
    let val = element.parentElement.innerText.trim(); // we fetch the value of sibling input
    if (val === ""){ // if this value is empty -> return
        console.log("Please input value");
        // add notify
        return;
    }
    switch (element.parentElement.id) { // thanks to the ID, we're checking which input is concerned
        // then for each case we're adding their respected char (@ # or -), it will be then used in ./src/Controller/AjaxController.php
        case "sPseudo":
            keyword = "@" + val;
            break;
        case "sHash":
            keyword = "#" + val;
            break;
        case "sBlack":
            keyword = "-" + val;
            break;
        default:
            console.log("Error while processing keyword");
            // add notify
            break;
    }
    console.log(keyword);
    console.log();
    $.ajax({
        type:'DELETE',
        data:{'keyword':keyword},
        url:'/ajax/search_settings',
        complete:function(data,status){
            if(data.status !== 200){
                console.log(data);
                console.log('Not response 200');
                return;
            }
            //console.log('COMPLETE:')
            console.log(data.responseJSON.output);
            console.log(data.status);
            $.notify({
                    icon:'fa fa-check-circle',
                    title: "<strong>Success :</strong> ",
                    message:data.responseJSON.output // data below
                },
                {
                    type:'success',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
                element.parentElement.parentElement.removeChild(element.parentElement); 
           
        },
        error:function(jqXHR, textStatus) {
            //console.log('ERROR');
            //console.log(jqXHR.responseJSON);
            //console.log(jqXHR.responseJSON.status);
            $.notify({
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.responseJSON.output
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        }
    });
}

// set bot config
function config() {
   
    $.ajax({
        type:'post',
        data:$("[name=configForm]").serialize() ,
        url:'/ajax/config_bot',
       
        success: function(data){
            console.log(data.output);
             $.notify(
                 {
                     icon:'fa fa-check-circle',
                     title: "<strong>Success :</strong> ",
                     message:'Configuration sauvgarder'
                 },
                 {
                     type:'success',
                     delay: 5000,
                     timer: 1000,
                     offset: 50
                 });
        },
        error: function(response) {
                      
                       //on affiche les erreurs...
            $.notify(
                {
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.status+" "+errorThrown
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                }
             );
        },
    });
}

// turn on/off a bot   
function run_bot(toggle) {
   

    if($(toggle).prop("checked") === true){
        var status=true;//set value on
    }else{
        var status=false;//set value off
    }
    //ajax server request
    $.ajax({
        type:'post',
        data:{"status":status} ,
        url:'/ajax/set_bot_status',

        success:function(data){
            $.notify(
                {
                    icon:'fa fa-check-circle',
                    title: "<strong>Success :</strong> ",
                    message:data.output
                },
                {
                    type:'success',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if($(toggle).prop("checked") === true){
                toggle.checked=false;
            }else{
                toggle.checked=true;
            }
            $.notify(
                {
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.status+" "+errorThrown
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        },
    });
}



runTestIgAcc = () => {
    let username = $('#form_username').val();
    let password = $('#form_password').val();
    if(username === "" || password === ""){
        $.notify(
            {
                icon:'fa fa-exclamation-circle',
                title: "<strong>Form data</strong> ",
                message:"Username or password empty"
            },
            {
                type:'danger',
                delay: 3000,
                timer: 1000,
                offset: 50
            }
        );
        return;
    }
    $.ajax({
        'type':'post',
        'data':{
            'username':username,
            'password':password
        },
        'url':'/instagui/testIgAccount',
        complete:function(data,status){
            if(data.status !== 200){
                return;
            }
            //console.log('COMPLETE:')
            //console.log(data.responseJSON.output);
            //console.log(data.status);
            $.notify({
                icon:'fa fa-check-circle',
                title: "<strong>Success :</strong> ",
                message:data.responseJSON.output // data below
            },
            {
                type:'success',
                delay: 5000,
                timer: 1000,
                offset: 50
            });
        },
        error:function(jqXHR, textStatus) {
            //console.log('ERROR');
            //console.log(jqXHR.responseJSON);
            //console.log(jqXHR.responseJSON.status);
            $.notify({
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.responseJSON.output
            },
            {
                type:'danger',
                delay: 5000,
                timer: 1000,
                offset: 50
            });
        }

    });
    return true;
}


//set slot status on
function activer(tmpid) {
    //ajax server request
    $.ajax({
       type:'post',
       data:{"slot":tmpid,"value":'on'} ,
       url:'/ajax/set_slot',

       success:function(data){
           document.getElementById(tmpid).className = "btn btn-primary";
           $.notify(
               {
                   icon:'fa fa-check-circle',
                   title: "<strong>Success :</strong> ",
                   message:"slot activate"
               },
               {
                   type:'success',
                   delay: 5000,
                   timer: 1000,
                   offset: 50
               });
       },
       error: function(jqXHR, textStatus, errorThrown) {
          
           $.notify(
               {
                   icon:'fa fa-exclamation-circle',
                   title: "<strong>"+textStatus+" :</strong> ",
                   message:jqXHR.status+" "+errorThrown
               },
               {
                   type:'danger',
                   delay: 5000,
                   timer: 1000,
                   offset: 50
               });
       },
   }); 
    //hide modal window
   $('#exampleModal').modal('hide');
  
}
//set slot status off 
function desactiver(tmpid) {
    //ajax server request
    $.ajax({
       type:'post',
       data:{"slot":tmpid,"value":'off'} ,
       url:'/ajax/set_slot',

       success:function(data){
           document.getElementById(tmpid).className = "btn btn-danger";
           $.notify(
               {
                   icon:'fa fa-check-circle',
                   title: "<strong>Success :</strong> ",
                   message:"slot deactivated"
               },
               {
                   type:'success',
                   delay: 5000,
                   timer: 1000,
                   offset: 50
               });
       },
       error: function(jqXHR, textStatus, errorThrown) {
          
           $.notify(
               {
                   icon:'fa fa-exclamation-circle',
                   title: "<strong>"+textStatus+" :</strong> ",
                   message:jqXHR.status+" "+errorThrown
               },
               {
                   type:'danger',
                   delay: 5000,
                   timer: 1000,
                   offset: 50
               });
       },
   }); 
//hide modal window
$('#exampleModal').modal('hide');
}

editProfile = () => {
    $.ajax({
        type:'post',
        data:$("[name=profile]").serialize() ,
        url:'/ajax/edit_profile',
       
        success: function(data){
            console.log(data.output);
             $.notify(
                 {
                     icon:'fa fa-check-circle',
                     title: "<strong>Success :</strong> ",
                     message:'Configuration sauvgarder'
                 },
                 {
                     type:'success',
                     delay: 5000,
                     timer: 1000,
                     offset: 50
                 });
        },
        error: function(response) {
                      
                       //on affiche les erreurs...
            $.notify(
                {
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.status+" "+errorThrown
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                }
             );
        },
    });
}

testProxy = (element) => {
    console.log(element.parentElement.children[1].value);
    let proxy = element.parentElement.children[1].value;
    let val = proxy.trim(); // we fetch the value of sibling input
    console.log(element.id);
    $.ajax({
        type:'POST',
        data:{'proxy':val+''},
        url: element.id === "add" ? '/ajax/proxy' : '/instagui/testProxy',
        complete:function(data,status){
            if(data.status !== 200){
                console.log(data);
                console.log('Not response 200');
                return;
            }
            console.log(data.responseJSON.output);
            console.log(data.status);
            $.notify({
                    icon:'fa fa-check-circle',
                    title: "<strong>Success :</strong> ",
                    message:data.responseJSON.output // data below
                },
                {
                    type:'success',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        },
        error:function(jqXHR, textStatus) {
            console.log(jqXHR);
            $.notify({
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.responseJSON.output
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        }
    });
}

