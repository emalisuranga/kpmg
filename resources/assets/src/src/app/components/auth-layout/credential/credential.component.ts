import { ToastrService } from 'ngx-toastr';
import { Component, OnInit } from '@angular/core';
import { FormGroup, Validators, FormControl } from '@angular/forms';
import { Router } from '@angular/router';
import { DataService } from '../../../storage/data.service';
import { AuthenticationService } from '../../../http/services/authentication.service';
import { compareValidator } from '../../../directive/validator/compare.directive';
import { onIRegWithCred, ICredential } from '../../../http/models/register.model';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-credential',
  templateUrl: './credential.component.html',
  styleUrls: ['./credential.component.scss']
})
export class CredentialComponent implements OnInit {

  public formGroup: FormGroup;
  showSpinner = false;

  constructor(
    private router: Router,
    private AuthService: AuthenticationService,
    private data: DataService,
    private toastr: ToastrService,
    private spinner: NgxSpinnerService
  ) {

  }

  ngOnInit() {
    this.formGroup = new FormGroup({
      'email': new FormControl('', [
        Validators.email,
        Validators.required
      ]),
      'password': new FormControl('', Validators.required),
      'confirmpassword': new FormControl('', [
        Validators.required,
        compareValidator('password')
      ])
    });
  }

  get email() { return this.formGroup.get('email'); }

  get password() { return this.formGroup.get('password'); }

  get confirmpassword() { return this.formGroup.get('confirmpassword'); }

  onSubmit(): void {

    if (this.formGroup.invalid) {
      return;
    }

    this.spinner.show();

    const user: ICredential = {
      email: this.email.value.toLowerCase(),
      password: this.password.value,
      password_confirmation: this.confirmpassword.value
    };

    const onRegWithCred: onIRegWithCred = {
      registerData: this.data.regData,
      credential: user
    };

    const main_form: FormData = new FormData();
    main_form.append('avater', <File>this.data.regData.details.avater);

    main_form.append('Info', JSON.stringify(onRegWithCred));

    this.AuthService.auRegister(main_form)
      .subscribe(
        req => {
          this.router.navigate(['user/activation']);
          this.toastr.success('Registration Successful!', 'Success');
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.toastr.error('Registration Unsuccessful!', 'Error');
        }
      );
  }
}
