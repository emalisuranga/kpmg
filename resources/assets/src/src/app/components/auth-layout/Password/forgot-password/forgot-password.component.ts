import { NgxSpinnerService } from 'ngx-spinner';
import swal from 'sweetalert2';
import { AuthenticationService } from './../../../../http/services/authentication.service';
import { FormGroup, FormBuilder, FormControl, Validators } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss']
})
export class ForgotPasswordComponent implements OnInit {

  public ResetLink: FormGroup;

  constructor(
    private authService: AuthenticationService,
    private formBuilder: FormBuilder,
    private spinner: NgxSpinnerService,
    private router: Router,
  ) { }

  ngOnInit() {
    this.ResetLink = this.formBuilder.group({
      email: new FormControl(null, [
        Validators.email,
        Validators.required
      ]),
    });
  }

  get email() { return this.ResetLink.get('email'); }

  onSubmit() {
    if (this.ResetLink.invalid) { return; }
    this.spinner.show();
    this.authService.auRequestResetLink(this.email.value)
      .subscribe(
        req => {
          if (req['status'] === true) {
            this.router.navigate(['user/migrate']);
          } else {
            this.spinner.hide();
            swal({
              title: 'Success',
              text: req['message'],
              type: 'success',
              showCancelButton: false,
              confirmButtonColor: '#3085d6',
              confirmButtonText: 'Ok',
              allowOutsideClick: false
            }).then((result) => {
              if (result.value) {
                this.router.navigate(['/home']);
              }
            });
          }
        },
        error => {
          this.spinner.hide();
          swal('error!', error['message'], 'error');
        }
      );

  }

}
