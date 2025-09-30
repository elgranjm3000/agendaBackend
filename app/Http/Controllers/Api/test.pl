 if($datos->lv_Resultado != "Error"){

            $usuarios = $this->user->versalas($datos->lv_ID); 
            $users = $this->user->sp_email($datos->lv_email);
            $channel = $this->user->sp_chanel($users->id_user);

            $this->session->set_userdata('id_user', $users->id_user);
            $this->session->set_userdata('id', $users->id_user);
            $this->session->set_userdata('email', $users->email);
            $this->session->set_userdata('nombre', $users->name." ".$users->last_name);
            $this->session->set_userdata('id_office', $users->id_office);
            $this->session->set_userdata('email_system', $users->email_system);
            $this->session->set_userdata('email_notifications', $users->email_notifications);
            $this->session->set_userdata('number_whatsapp', $users->number_whatsapp);
            $this->session->set_userdata('rut_number', $users->rut_number);
            $this->session->set_userdata('rut_validation', $users->rut_validation);
            $this->session->set_userdata('number_phone', $users->number_phone);
            $this->session->set_userdata('avatar', $users->avatar);
            $this->session->set_userdata('usernamelistado', $usuarios);
            $this->session->set_userdata('id_rol', $users->id_rol);
            $this->session->set_userdata('username', $users->username);
            $this->session->set_userdata('lock', $users->numberlock);
            $this->session->set_userdata('id_manager', $users->id_manager);
            $this->session->set_userdata('print_mode', $users->print_mode);
            $this->session->set_userdata('attention_mode', $users->attention_mode);
            $this->session->set_userdata('id_channel', $channel->channel);

            $usuariosusername = $this->user->getid();
            $this->session->set_userdata('usuariosusername', $usuariosusername);
            $fechahoy = date("d-m-Y H:i:s");
            $this->user->sp_lastlogin($fechahoy,$users->id_user);
            $oficina = $this->parameters->get_officeById($users->id_office);
            $roles   = $this->parameters->get_rolById($users->id_rol);

            $this->session->set_userdata('nombreoficina',$oficina->name);
            $this->session->set_userdata('nombreroles',$roles->descripcion);
               
            $currentUser = array(
                'id' => $users->id_user,
                'email' => $users->email,
                'nombre' => $users->name." ".$users->last_name,
                'id_rol' => $users->id_rol
            );

            $this->session->set_userdata('current_user', $currentUser);

            redirect( base_url() . "access"); 

        }