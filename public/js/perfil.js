document.getElementById('nombreUser').innerHTML='Nombre Usuario: <b><u>'+localStorage.getItem('username')+'</u></b>'
let msjAlert = document.getElementById('alert');
function cargarAmigos(){
    let nombreUsuario = localStorage.getItem('username');

    if (nombreUsuario) {
        cargarInformacion();
        function cargarInformacion(){
        let url = `http://localhost/matchfilm/api/post_amigos.php`;
        let options={
            method: 'GET',
            headers:{
                'Authorization': `${localStorage.getItem('token')}`,
            }
        }
        fetch(url,options)
            .then(res => {
                if(res.status == 200){
                    return res.json();
                }else{
                    let amigos = document.getElementById('amigo');
                    amigos.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">No tienes pareja</h5>
                            <input type="text" id="nombreAmigo" class="form-control" placeholder="Nombre de usuario"><br>
                            <p id="usernameError"></p>
                            <button type="submit" id="btnAgregarAmigo" class="btn btn-primary">Agrega a tu pareja</button>
                        </div>`;
                        close
                }
            })
            .then(data => {
                if (data.length==0){
                    let amigos = document.getElementById('amigo');
                    amigos.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">No tienes pareja</h5>
                            <input type="text" id="nombreAmigo" class="form-control" placeholder="Nombre de usuario"><br>
                            <p id="usernameError"></p>
                            <button type="submit" id="btnAgregarAmigo" class="btn btn-primary">Agrega a tu pareja</button>
                        </div>`;
                        close
                }else{
                amigos = document.getElementById('amigo');
                if (data[0].nombre_amigo==localStorage.getItem('username')){
                    amigos.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><b>${data[0].nombre_usuario}</b></h5>
                            <button type="submit" onclick="eliminarAmigo('${data[0].nombre_usuario}')" class="btn btn-danger">Eliminar Pareja</button>
                        </div>
                    </div>`;
                }else{
                    amigos.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><b>${data[0].nombre_amigo}</b></h5>
                            <button type="submit" onclick="eliminarAmigo('${data[0].nombre_amigo}')" class="btn btn-danger">Eliminar Pareja</button>
                        </div>
                    </div>`;
                }
            }
                
            }).finally(()=>{
                document.getElementById('nombreAmigo').onblur = function() {
                    let nombreAmigoInput = this.value.trim();
                    let usernameError = document.getElementById('usernameError');
                    
                    if (nombreAmigoInput.length < 5) {
                        usernameError.innerHTML = 'El nombre de usuario debe tener al menos 5 letras.';
                        usernameError.style.color = 'red';
                        
                    } else {
                        usernameError.innerHTML = '';
                        document.getElementById('btnAgregarAmigo').addEventListener('click' ,function() {
                            agregarAmigo(nombreAmigoInput)
                        })
                    }
                };
            })
            .catch(error => {
                console.error(error);
            });
        }
    } else {
        console.error('No se encontró el nombre de usuario en el almacenamiento local');
    }
}
cargarAmigos();
cargarNotificaciones();

document.getElementById('editarUsuario').addEventListener('click', function() {
    let url = `http://localhost/matchfilm/api/get_infoUsuario.php`;
    let options = {
        method: 'GET',
        headers: {
            'Authorization': `${localStorage.getItem('token')}`,
        },
    };
    fetch(url, options)
        .then(res => {
            if (res.status == 200) {
                return res.json();
            }else {
                throw new Error('Error al obtener la información del usuario');
            }
        })
        .then(data => {
            document.getElementById('nombreUsuario').value = data.username;
            document.getElementById('emailUsuario').value = data.email;
        })
        .catch(error => {
            console.error(error);
        });

    var myModal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
    myModal.show();
});
document.getElementById('btnEditarUsuario').addEventListener('click', function() {
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(document.getElementById('nombreUsuario').value.length < 5 ||
     !emailRegex.test(document.getElementById('emailUsuario').value) ||
      !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{5,}$/.test(document.getElementById('contrasenaUsuario').value)){
        document.getElementById('alertModal').innerHTML=`
                    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                        <strong>Campos incompletos!</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
    }else{
        editarUsuario();
    }
});
 function editarUsuario(){
    let usuario = new FormData();
            usuario.append('username', document.getElementById('nombreUsuario').value.trim())
            usuario.append('email', document.getElementById('emailUsuario').value.trim())
            usuario.append('password', document.getElementById('contrasenaUsuario').value.trim())
            if(document.getElementById('imagenUsuario').files[0]){
                usuario.append('image', document.getElementById('imagenUsuario').files[0])
            }
    
    console.log(usuario)
    let url = 'http://localhost/matchfilm/api/get_infoUsuario.php';
    const options = {
        method: 'POST',
        headers: {
            'Authorization': `${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data'
        },
        body: usuario
    };
    fetch(url, options)
        .then(res => {
            console.log(res);
            if (res.status == 200) {
                return res.json(); 
            }else {
                throw new Error('Error al actualizar el usuario');
            }
        })
        .then(data => {
            obtenerNuevoToken(data.username)
            console.log(data);
            localStorage.removeItem('username');
            localStorage.setItem('username', data.username);
            document.getElementById('alertModal').innerHTML=`
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <strong>Usuario editado correctamente!</strong>
                        <button type="button" class="btn btn-primary" onclick="reloadPage()">Recargar Página</button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;

        }) 
        .catch(error => {
            console.error(error);
        })
 }
 function reloadPage(){
    location.reload();
 };
function agregarAmigo(){
    let nuevoUser = {
        'nombre_amigo' : document.getElementById('nombreAmigo').value,
        'notificacion' : "amistad",
    }
    
    let url = 'http://localhost/matchfilm/api/post_notificacion.php';
    const options = {
        method: 'POST',
        headers: {
            'Authorization': `${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(nuevoUser)
    };
    fetch(url, options)
        .then(res => {
            if (res.status == 200) {
                return res.json(); 
            }
        })
        .then(data => {
            msjAlert.innerHTML=`
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <strong>Solicitud de amistad enviada Correctamente!</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>` 
        }) 
    }

    
    function eliminarAmigo(nombreAmigo){
        console.log(nombreAmigo);
        let amigos = {
            "nombre_amigo": nombreAmigo
        }
        let url = `http://localhost/matchfilm/api/eliminarAmigos.php`;
        let options= {
            method: 'POST',
            headers: {
                'Authorization': `${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(amigos)
        }
        fetch(url, options)
        .then(res => {
            if(res.status == 201){
                return res;
            }else{
                msjAlert.innerHTML=`
                <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    <strong>Error al eliminar pareja!</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>` 
            }
        })
        .then(data => {
            msjAlert.innerHTML=`
            <div class="alert alert-sucess alert-dismissible fade show text-center" role="alert">
                <strong>Pareja eliminada!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>` 
            cargarAmigos();
            cargarNotificaciones()
        })
        .catch(error => {
            console.error(error);
        })
    }
    function cargarNotificaciones(){

        if (localStorage.getItem('token')) {
            let url = `http://localhost/matchfilm/api/post_notificacion.php`;
            let options = {
                method: 'GET',
                headers: {
                    'Authorization': `${localStorage.getItem('token')}`,
                },
            };            
            fetch(url, options)
                .then(res => {
                    let notificaciones = document.getElementById('notificaciones');
                        notificaciones.innerHTML = '';
                    if (res.status == 200){
                        return res.json();
                    }else{
                        let notificaciones = document.getElementById('notificaciones');
                        notificaciones.innerHTML = 'No tienes notificaciones';
                    }
                })
                .then(data => {
                    console.log(data);
                    if (data){
                        data.forEach(notificacion => {
                            if (notificacion.notificacion == 'amistad' && notificacion.nombre_usuario == notificacion.username){

                            }else if(notificacion.notificacion == 'amistad' && notificacion.nombre_usuario != notificacion.username){
                                notificaciones.innerHTML += `
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><b>${notificacion.nombre_usuario}</b></h5>
                                        <p class="card-text">${notificacion.notificacion}</p>
                                        <a href="#" onclick="aceptarAmigo('${notificacion.nombre_usuario}')" class="btn btn-primary">Aceptar</a>
                                        <a href="#" onclick="eliminarNotificacion('${notificacion.nombre_usuario}','${notificacion.nombre_amigo}','${notificacion.notificacion}')" class="btn btn-danger">Rechazar</a>
                                    </div>
                                </div>`;
                            }else if (notificacion.nombre_usuario != notificacion.username){
                                notificaciones.innerHTML += `
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><b>${notificacion.notificacion}</b></h5>
                                        <p class="card-text">Con: ${notificacion.nombre_usuario}</p>
                                    </div>
                                </div>`;
                                eliminarNotificacion(`${notificacion.nombre_usuario}`, `${notificacion.nombre_amigo}`, `${notificacion.notificacion}`)
                            }else{
                                notificaciones.innerHTML += `
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><b>${notificacion.notificacion}</b></h5>
                                        <p class="card-text">Con: ${notificacion.nombre_amigo}</p>
                                    </div>
                                </div>`;
                                eliminarNotificacion(`${notificacion.nombre_amigo}`, `${notificacion.nombre_usuario}`, `${notificacion.notificacion}`)
                            }
                              
                        }); 
                    }else{
                        let notificaciones = document.getElementById('notificaciones');
                        notificaciones.innerHTML = 'No tienes notificaciones';
                    }  
                });
        } else {
            window.location='index.php';
        }
        
    };

    function aceptarAmigo(nombre_amigo){

        if (nombreUsuario) {
            let url = `http://localhost/matchfilm/api/post_amigos.php`;
            let options={
                method: 'GET',
                headers:{
                    'Authorization': `${localStorage.getItem('token')}`,
                    "Content-Type": "application/json" 
                }
            }
            fetch(url,options)
                .then(res => {
                    if(res.status == 200){
                        msjAlert.innerHTML=`
                        <div class="alert alert-sucess alert-dismissible fade show text-center" role="alert">
                            <strong>Ya tienes una pareja, no puedes agregar más!</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`
                    }else{
                        let nuevoAmigo = {
                            'nombre_amigo' : nombre_amigo,
                        }
                        
                        let url = 'http://localhost/matchfilm/api/gestionAmigos.php';
                        const options = {
                            method: 'POST',
                            headers: {
                                'Authorization': `${localStorage.getItem('token')}`,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(nuevoAmigo)
                        };
                        fetch(url, options)
                            .then(res => {
                                if (res.status == 200) {
                                    return res.json(); 
                                }
                            })
                            .then(data => {
                                msjAlert.innerHTML=`
                                    <div class="alert alert-sucess alert-dismissible fade show text-center" role="alert">
                                        <strong>Pareja agregada correctamente!</strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`
                
                            }).finally(()=>{
                                console.log(nombreUsuario + nombre_amigo);
                                eliminarNotificacion(nombre_amigo, localStorage.getItem('username') , 'amistad')
                                cargarNotificaciones();
                                cargarAmigos();
                            })
                    }
                })
                
        }
    }
                               
    function eliminarNotificacion(nombre_usuario, nombre_amigo, notificacion) {
        let eliminar = {
            "nombre_usuario": nombre_usuario,
            "nombre_amigo": nombre_amigo,
            "notificacion": notificacion
        };
    
        let url = `http://localhost/matchfilm/api/delete_notificacion.php`;
        const opciones = {
            method: 'POST',
            headers: {
                'Authorization': `${localStorage.getItem('token')}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(eliminar)
        };
    
        fetch(url, opciones)
            .then(response => {
                if (response.ok) {
                    return response.json(); // Devuelve la promesa para manejar los datos JSON
                } else {
                    throw new Error('Error en la solicitud');
                }
            })
            .then(datos => {
                // Manejar los datos de respuesta si es necesario
                console.log(datos); // Muestra los datos devueltos por la API
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    function obtenerNuevoToken(usuario) {
        fetch(`http://localhost/matchfilm/api/generar_token.php?username=${usuario}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        })
        .then(res => {
            if (res.ok) {
                return res.json();
            } else {
                throw new Error('Error al obtener el nuevo token');
            }
        })
        .then(data => {
            localStorage.removeItem('token')
            localStorage.setItem('token', data.token); // Actualizar el token en localStorage
            console.log('Token actualizado:', data.token);
            // Aquí puedes continuar con las operaciones que requieren el nuevo token
        })
        .catch(error => {
            console.error('Error en la solicitud de nuevo token:', error);
        });
    }
                               
    if(localStorage.getItem('token')){
        document.getElementById('cerrarSesión').addEventListener('click', cerrarSesion)
        function cerrarSesion(){
            localStorage.clear();
            window.location.href = './index.php';
        }
    }else{
        localStorage.removeItem('token');        
        localStorage.removeItem('username');    
        msjAlert.innerHTML=`
                        <div class="alert alert-sucess alert-dismissible fade show text-center" role="alert">
                            <strong>Necesita iniciar sesion!</strong>
                            <a href="./login.php" class="btn btn-primary mr-2">Ir al inicio de sesión</a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`    
    }
        
