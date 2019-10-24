import { AppComponent } from './../../../app.component';
import { GlobleUserService } from './../../../http/shared/globle.user.service';
import { Component, OnInit, ViewChild } from '@angular/core';
import { AbstractControl, FormGroup, FormControl, Validators, FormBuilder } from '@angular/forms';
import { AuthenticationService } from '../../../http/services/authentication.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { Router } from '@angular/router';
import { IAuth } from '../../../http/models/auth.model';
import { ModalDirective } from 'angular-bootstrap-md';
import { ToastrService } from 'ngx-toastr';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-sign-in',
  templateUrl: './sign-in.component.html',
  styleUrls: ['./sign-in.component.scss']
})
export class SignInComponent implements OnInit {
  @ViewChild('frame') modal: ModalDirective;

  showSpinner: boolean;
  showPassword: boolean = true;
  public formGroup: FormGroup;
  isLoginError: boolean;

  private auth: IAuth;

  showIncompleteActions = environment.showIncompleteActions;

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    private AuthService: AuthenticationService,
    private spinner: NgxSpinnerService,
    private user: GlobleUserService,
    private main: AppComponent,
    private snotifyService: ToastrService
  ) { }

  public ngOnInit() {
    this.isLoginError = false;
    this.formGroup = this.formBuilder.group({
      email: new FormControl(null, [
        Validators.email,
        Validators.required
      ]),
      password: new FormControl(null, [Validators.required])
    });
  }

  get email() { return this.formGroup.get('email'); }

  get password() { return this.formGroup.get('password'); }

  showModal() {
    this.showPassword = true;
    this.modal.show();
  }

  onSubmit() {

    if (this.formGroup.invalid) {
      return;
    }
    this.spinner.show();
    this.isLoginError = false;
    const authData: IAuth = {
      email: this.email.value.trim(),
      password: this.password.value,
      clEmail: false
    };

    this.AuthService.aulogin(authData)
      .subscribe(
        req => {
          this.isLoginError = false;
          this.router.navigate(['/dashboard/home']);
          this.resetForm(this.formGroup);
          this.user.setUserData();
          this.user.setAvater();
          this.main.time();
          this.spinner.hide();
          this.modal.hide();
        },
        error => {
          this.spinner.hide();
          this.isLoginError = true;
        }
      );
  }

  resetForm(formGroup: FormGroup) {
    let control: AbstractControl = null;
    formGroup.reset();
    formGroup.markAsUntouched();
    Object.keys(formGroup.controls).forEach((name) => {
      control = formGroup.controls[name];
      control.setErrors(null);
    });
  }

  onKeydown() {
    this.migratedAccount();
  }

  migratedAccount() {
    if (this.email.invalid) {
      this.snotifyService.error('Please Enter your email address', 'Error');
      return true;
    }

    this.spinner.show();
    const authData: IAuth = {
      email: this.email.value.trim(),
      clEmail: true
    };
    this.AuthService.auIsOldMember(authData)
      .subscribe(
        req => {
          this.spinner.hide();
          if (req['status'] === true) {
            this.modal.hide();
            this.router.navigate(['user/migrate']);
          } else {
            this.showPassword = false;
          }
        },
        error => {
          this.spinner.hide();
          this.isLoginError = true;
        }
      );
  }

}
