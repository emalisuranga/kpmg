import { Component, OnInit } from '@angular/core';
import { DataService } from 'src/app/storage/data.service';
import { ICountry } from 'src/app/http/models/general.model';
import { FormBuilder, FormGroup, FormControl, Validators } from '@angular/forms';
import { NicValidator, compareValidator } from 'src/app/directive/validator/compare.directive';
import { ToastrService } from 'ngx-toastr';
import { IRegister, onIRegWithCred, ICredential, IAddress } from 'src/app/http/models/register.model';
import { AuthenticationService } from 'src/app/http/services/authentication.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { Router, ActivatedRoute } from '@angular/router';
import { AppLoadService } from 'src/app/http/shared/app-load.service';

@Component({
  selector: 'app-migrete-user-info',
  templateUrl: './migrete-user-info.component.html',
  styleUrls: ['./migrete-user-info.component.scss']
})
export class MigreteUserInfoComponent implements OnInit {

  public registerForm: FormGroup;
  public credential_group: FormGroup;
  public localAddress: FormGroup;
  public ForignAddress: FormGroup;

  private email: string;
  private token: string;

  private addressData: Array<IAddress> ;

  imageUrl = 'assets/images/avatar/user-avatar.png';
  public fileToUpload: File;
  search: any;
  // title = null;

  public country: Array<ICountry> = [];
  localUser = false;

  constructor(
    public data: DataService,
    private snotifyService: ToastrService,
    private AuthService: AuthenticationService,
    private toastr: ToastrService,
    private spinner: NgxSpinnerService,
    private router: Router,
    private activatedroute: ActivatedRoute,
    private AppLoad: AppLoadService
  ) { }

  ngOnInit() {
    this.spinner.show();
    this.AppLoad.getCountry()
      .subscribe(
        req => {
          this.country = req;
        }
      );
    this.__getParm();
    this.__validation();
    this.spinner.hide();
  }

  __getParm() {
    this.activatedroute.queryParams
      .subscribe(params => {
        this.email = params.email.trim();
        this.token = params.token;
      });
    if (this.email && this.token) {
      this.AuthService.auMigrateActivation(this.email, this.token).subscribe(
        req => {
          this.f.title.setValue(req['user'].key + '');
          this.f.fname.setValue(req['user'].first_name  === null ? '' : req['user'].first_name + '');
          this.f.lname.setValue(req['user'].last_name === null ? '' : req['user'].last_name + '');
          this.f.oname.setValue(req['user'].other_name === null ? '' : req['user'].other_name + '');
          this.f.nic.setValue(req['user'].nic === null ? '' : req['user'].nic + '');
          this.f.passportnumber.setValue(req['user'].passport_no + '');
          this.f.piCountry.setValue(req['user'].passport_issued_country + '');
          this.f.occupation.setValue(req['user'].occupation === null ? '' : req['user'].occupation + '');
          this.f.mobileNumber.setValue(req['user'].mobile === null ? '' : req['user'].mobile + '');
          this.f.telNumber.setValue(req['user'].telephone === null ? '' : req['user'].telephone + '');


          if (req['localAddress'] != null) {
            if (req['localAddress'].address_id !== null) {
              this.f.lcCountry.setValue(req['localAddress'].country + '');
              this.f.lcAddressId.setValue(req['user'].address_id + '');
              this.f.lcAddress01.setValue(req['localAddress'].address1 === null ? '' : req['localAddress'].address1 + '');
              this.f.lcAddress02.setValue(req['localAddress'].address2 === null ? '' : req['localAddress'].address2 + '');
              this.f.lcProvince.setValue(req['localAddress'].province + '');
              this.f.lcDistrict.setValue(req['localAddress'].district + '');
              this.f.lcCity.setValue(req['localAddress'].city + '');
              this.f.lcGNDivision.setValue(req['localAddress'].gn_division === null ? '' : req['localAddress'].gn_division + '');
              this.f.lcPostCode.setValue(req['localAddress'].postcode === null ? '' : req['localAddress'].postcode + '');
            }
          }

          if (req['foreignAddress'] != null) {
            if (req['foreignAddress'].foreign_address_id !== null) {
              this.f.frCountry.setValue(req['foreignAddress'].country + '');
              this.f.frAddressId.setValue(req['user'].foreign_address_id + '' );
              this.f.frAddress01.setValue(req['foreignAddress'].address1 === null ? '' : req['foreignAddress'].address1 + '');
              this.f.frAddress02.setValue(req['foreignAddress'].address2 === null ? '' : req['foreignAddress'].address2 + '');
              this.f.frPostCode.setValue(req['foreignAddress'].postcode === null ? '' : req['foreignAddress'].postcode + '');
            }
          }

          if (req['user'].is_srilankan === 'yes'){
            this.localUser = true;
            this.f.lcCountry.setValue('Sri Lanka');

          } else {
            this.localUser = false;
            this.f.lcCountry.setValue('');
          }

          this.f.valemail.setValue(req['user'].email + '');
          this.f.valemail.disable();

          // this.__validation();
        },
        error => {
          this.router.navigate(['/home']);
        }
      );
    }
  }

  __validation() {

    this.registerForm = new FormGroup({
      title: new FormControl(null, [Validators.required]),
      fname: new FormControl(null, [Validators.required, Validators.pattern('[a-zA-Z ]*')]),
      lname: new FormControl(null, [Validators.required, Validators.pattern('[a-zA-Z ]*')]),
      oname: new FormControl(null, [Validators.pattern('[a-zA-Z ]*')]),
      avaterFile: new FormControl(null),

      nic: new FormControl(null, [Validators.required, NicValidator('nic')]),
      ckHasPassport: new FormControl(false),
      passportnumber: new FormControl(null, Validators.pattern('[0-9a-zA-Z]+')),

      piCountry: new FormControl(null),
      occupation: new FormControl(null),
      mobileNumber: new FormControl(null, [Validators.required]),
      telNumber: new FormControl(null),
      lcCountry: new FormControl('Sri Lanka', Validators.required),
      lcAddressId: new FormControl(null),
      lcAddress01: new FormControl(null, Validators.required),
      lcAddress02: new FormControl(null, Validators.required),
      lcProvince: new FormControl(null, Validators.required),
      lcDistrict: new FormControl(null, Validators.required),
      lcCity: new FormControl(null, Validators.required),
      lcGNDivision: new FormControl(null, Validators.required),
      lcPostCode: new FormControl(null, Validators.required),
      ckWantFrAddress: new FormControl(false),
      ckWantlcAddress: new FormControl(false),
      frAddressId: new FormControl(false),
      frAddress01: new FormControl(null),
      frAddress02: new FormControl(null),
      frCountry: new FormControl(null),
      frPostCode: new FormControl(null),

      valemail: new FormControl('', Validators.compose([
        Validators.required,
        Validators.email
      ])),
      password: new FormControl('', Validators.compose([
        Validators.minLength(5),
        Validators.required
      ])),
      confirmPassword: new FormControl('', [Validators.required, compareValidator('password')])
    });
  }

  get f() { return this.registerForm.controls; }
  get title() { return this.registerForm.get('title'); }
  get fname() { return this.registerForm.get('fname'); }
  get lname() { return this.registerForm.get('lname'); }
  get oname() { return this.registerForm.get('oname'); }
  get nic() { return this.registerForm.get('nic'); }
  get passportnumber() { return this.registerForm.get('passportnumber'); }
  get mobileNumber() { return this.registerForm.get('mobileNumber'); }
  get telNumber() { return this.registerForm.get('telNumber'); }
  get lcCountry() { return this.registerForm.get('lcCountry'); }
  get lcAddressId() { return this.registerForm.get('lcAddressId'); }
  get lcAddress01() { return this.registerForm.get('lcAddress01'); }
  get lcAddress02() { return this.registerForm.get('lcAddress02'); }
  get lcProvince() { return this.registerForm.get('lcProvince'); }
  get lcDistrict() { return this.registerForm.get('lcDistrict'); }
  get lcCity() { return this.registerForm.get('lcCity'); }
  get lcGNDivision() { return this.registerForm.get('lcGNDivision'); }
  get lcPostCode() { return this.registerForm.get('lcPostCode'); }
  get valemail() { return this.registerForm.get('valemail'); }
  get password() { return this.registerForm.get('password'); }
  get confirmPassword() { return this.registerForm.get('confirmPassword'); }

  onSubmit() {
    if (!this.registerForm.valid) {
     return;
    }
    this.spinner.show();
    const registerData: IRegister = {
      title: this.f.title.value,
      firstname: this.f.fname.value,
      lastname: this.f.lname.value,
      otherName: this.f.oname.value,
      avater: this.fileToUpload,
      nic: this.f.nic.value,
      passportid: this.f.passportnumber.value,
      passportIssueCountry: this.f.piCountry.value,
      occupation: this.f.occupation.value,
      mobileNumber: this.f.mobileNumber.value,
      telephoneNumber: this.f.telNumber.value,
      isSrilanka: this.f.lcCountry.value === 'Sri Lanka' ? 'yes' : 'no'
    };

    this.addressData = [
      {
        addressId: this.f.lcAddressId.value,
        address01: this.f.lcAddress01.value,
        address02: this.f.lcAddress02.value,
        gndivision: this.f.lcGNDivision.value,
        city: this.f.lcCity.value != null ? this.f.lcCity.value  : this.f.lcCity.value.description_en,
        district: this.f.lcDistrict.value != null ? this.f.lcDistrict.value : this.f.lcDistrict.value.description_en,
        province: this.f.lcProvince.value != null ? this.f.lcProvince.value : this.f.lcProvince.value.description_en,
        country: this.f.lcCountry.value,
        postCode: this.f.lcPostCode.value
      },
      {
        addressId: this.f.frAddressId.value,
        address01: this.f.frAddress01.value,
        address02: this.f.frAddress02.value,
        gndivision: '',
        city: '',
        district: '',
        province: '',
        country: this.f.frCountry.value,
        postCode: this.f.frPostCode.value
      }
    ];

    this.data.regData = {
      details: registerData,
      address: this.addressData
    };


    const user: ICredential = {
      email: this.email,
      password: this.f.password.value,
      password_confirmation: this.f.confirmPassword.value
    };

    const onRegWithCred: onIRegWithCred = {
      registerData: this.data.regData,
      credential: user
    };

    const main_form: FormData = new FormData();
    main_form.append('avater', <File>this.data.regData.details.avater);

    main_form.append('Info', JSON.stringify(onRegWithCred));

    this.AuthService.auMigrateRegister(main_form)
      .subscribe(
        req => {
          this.router.navigate(['/home']);
          this.toastr.success('Registration Successful! Please Login', 'Success');
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.toastr.error('Registration Unsuccessful!', 'Error');
        }
      );
  }

  onFileRemove() {
    this.imageUrl = 'assets/images/avatar/user-avatar.png';
    this.fileToUpload = null;
  }

  onFileInput(fileInput: any, ): void {
    if (fileInput.target.files && fileInput.target.files[0]) {
      const reader = new FileReader();
      this.fileToUpload = fileInput.target.files[0];
      reader.onload = (event: any) => {
        this.imageUrl = event.target.result;
      };
      reader.readAsDataURL(fileInput.target.files[0]);
    }
  }

  getValue(Key: string) {
    var value = '';
    this.country.forEach(element => {
      if (element.id === Key) { value = element.name; }
    });
    return value;
  }

  isEmptyObject(obj) {
    return (obj === '{}');
  }
}
