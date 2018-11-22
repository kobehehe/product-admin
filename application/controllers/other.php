public function update()
    {
        //product id
        $id = $this->uri->segment(4);

        //if save button was clicked, get the data sent via post
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            //form validation
            $this->form_validation->set_rules('name', 'name', 'required');
            $this->form_validation->set_rules('first_line', 'first_line', 'required');
            $this->form_validation->set_rules('city', 'city', 'required|string');
            $this->form_validation->set_rules('state', 'state', 'required|string');
            $this->form_validation->set_rules('zip', 'zip', 'required');
            //$this->form_validation->set_rules('phone', 'phone', 'required|numeric');
            $this->form_validation->set_error_delimiters('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>', '</strong></div>');
            //if the form has passed through the validation
            if ($this->form_validation->run()) {

                $data_to_store = array(
                    'name' => $this->input->post('name'),
                    'first_line' => $this->input->post('first_line'),
                    'second_line' => $this->input->post('second_line'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'zip' => $this->input->post('zip'),
                    'country' => $this->input->post('country'),
                    'phone' => $this->input->post('phone'),
                    'message_from_buyer' => $this->input->post('message_from_buyer'),
                    'message_from_seller' => $this->input->post('message_from_seller'),
                );
                //if the insert has returned true then we show the flash message
                if ($this->orders_model->update_product($id, $data_to_store) == TRUE) {
                    $this->session->set_flashdata('flash_message', 'updated');
                } else {
                    $this->session->set_flashdata('flash_message', 'not_updated');
                }

                redirect('admin/orders/update/' . $id . '');

            }//validation run

        }

        //if we are updating, and the data did not pass trough the validation
        //the code below wel reload the current data

        //product data
        $data['product'] = $this->orders_model->get_product_by_id($id);
        //fetch manufactures data to populate the select field
        $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
        //load the view
        $data['main_content'] = 'admin/orders/edit';
        $this->load->view('includes/template', $data);

    }//update