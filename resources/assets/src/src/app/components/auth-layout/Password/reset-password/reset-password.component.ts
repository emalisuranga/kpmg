import { AuthService } from './../../../../http/shared/auth.service';
import swal from 'sweetalert2';
import { ActivatedRoute, Router } from '@angular/router';
import { FormGroup, FormControl, FormBuilder, Validators } from '@angular/forms';
import { AuthenticationService } from './../../../../http/services/authentication.service';
import { Component, OnInit } from '@angular/core';
import { compareValidator } from '../../../../directive/validator/compare.directive';
import { GlobleUserService } from '../../../../http/shared/globle.user.service';

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent implements OnInit {

  public newPasswordForm: FormGroup;
  private email: string;
  private token: string;
  public headerText: string;
  public sp: string;

  constructor(
    private authService: AuthenticationService,
    private formBuilder: FormBuilder,
    private activatedroute: ActivatedRoute,
    private router: Router,
    public Auth: AuthService,
    private user: GlobleUserService
  ) { }

  ngOnInit() {
    this.getParam();
    this.setValidation();
    this.setHeaderText();
  }

  setHeaderText() {
    if (this.sp === 'uir') {
      this.headerText = 'RESET PASSWORD';
    } else {
      this.headerText = 'CREATE PASSWORD';
    }
  }

  setValidation() {
    this.newPasswordForm = this.formBuilder.group({
      newPassword: new FormControl(null, [
        Validators.required
      ]),
      confirmpassword: new FormControl(null, [
        Validators.required,
        compareValidator('newPassword')
      ])
    });
  }
  get newPassword() { return this.newPasswordForm.get('newPassword'); }

  get confirmpassword() { return this.newPasswordForm.get('confirmpassword'); }

  getParam() {
    this.activatedroute.queryParams
      .subscribe(params => {
        this.email = params.email;
        this.token = params.token;
        this.sp = params.sp;
      });
  }

  onSubmit() {
    const fdata: any = {
      email: this.email,
      token: this.token,
      password: this.newPassword.value,
    };

    this.authService.auNewResetPassword(fdata)
      .subscribe(
        req => {

          if (req['status'] === false && req['status'] !== undefined) {
            swal('error!', req['error'], 'error');
          } else {
            if (req['status'] !== undefined) {
              swal({
                title: 'Thank You',
                text: 'Password Set Successfully!',
                type: 'success',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false,
                confirmButtonText: 'Click here to proceed'
              }).then((result) => {
                if (result.value) {
                  this.Auth.setToken(req);
                  this.user.setUserData();
                  this.user.setAvater();
                  if (this.Auth.AuthGuard()) {
                    this.router.navigate(['/dashboard/home']);
                  }
                }
              });
            }
          }
        },
        error => {
          swal('error!', error['error'], 'error');
        }
      );
  }
}

