function checkAll(){
        var inputs = document.getElementsByTagName("input"); 
        for (var i = 0; i < inputs.length; i++) {  
                if (inputs[i].type == "checkbox" && document.getElementById('check_ctr').checked==true) {  
                        inputs[i].checked = true;  
                } else {
                        inputs[i].checked = false;      
                }   
        }   
}

