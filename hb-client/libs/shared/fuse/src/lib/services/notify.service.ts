import { Injectable } from '@angular/core';
import {
  SweetAlertArrayOptions,
  SweetAlertOptions,
  SweetAlertResult,
  SweetAlertType
} from 'sweetalert2';
import swal from 'sweetalert2';


@Injectable({
  providedIn: 'root'
})
export class NotifyService {

  constructor() { }


  showSuccess(title: string, message: string): void {
    swal(<SweetAlertOptions>{
      position: 'top-end',
      type: <SweetAlertType>'success',
      title: title,
      text: message,
      showConfirmButton: false,
      timer: 1500
    });
  }

  showError(title: string, message: string): void {
    swal(<SweetAlertOptions>{
      position: 'top-end',
      type: <SweetAlertType>'error',
      title: title,
      text: message,
      showConfirmButton: true,
      // timer:1500
    });

  }

  showPrompt(
    title: string,
    message: string,
    buttonText: string,
    action: any
  ): void {
    swal({
      title: title,
      text: message,
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#03a9f4',
      cancelButtonColor: '#f44336',
      confirmButtonText: buttonText
    }).then(action);
  }
}
