import { ToastrService } from 'ngx-toastr';
import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl } from '@angular/forms';
import { compareValidator } from '../../../../../../directive/validator/compare.directive';
import { AuthService } from '../../../../../../http/shared/auth.service';
import { AuthenticationService } from '../../../../../../http/services/authentication.service';

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.scss']
})
export class ChangePasswordComponent implements OnInit {
  changePasswordForm: FormGroup;
  constructor(
    public Auth: AuthService,
    private authService: AuthenticationService,
    private snotifyService: ToastrService
  ) { }

  ngOnInit() {

    this.changePasswordForm = new FormGroup({
      'oldPassword': new FormControl('', [Validators.required]),
      'newPassword': new FormControl('', [Validators.required]),
      'confirmPassword': new FormControl('', [Validators.required, compareValidator('newPassword')])
    });
  }


  get oldPassword() { return this.changePasswordForm.get('oldPassword'); }

  get newPassword() { return this.changePasswordForm.get('newPassword'); }

  get confirmPassword() { return this.changePasswordForm.get('confirmPassword'); }

  onSubmit() {

    if (this.changePasswordForm.invalid) { return; }

    const FormData: any = {
      email: this.Auth.getEmail(),
      oldPassword: this.oldPassword.value,
      confirmPassword: this.confirmPassword.value
    };

    this.authService.auPasswordChange(FormData)
      .subscribe(req => {
        this.changePasswordForm.reset();
        this.snotifyService.success('You have Successfully changed your Password.', 'success');
        this.authService.aulogout();
      },
      error => {
        this.changePasswordForm.reset();
        this.snotifyService.error('You have Unsuccessfully changed your Password.', 'error');
      });
  }
}
