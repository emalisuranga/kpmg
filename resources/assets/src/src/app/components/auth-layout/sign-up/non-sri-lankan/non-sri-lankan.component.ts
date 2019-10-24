import { IGNdivision, ICountry } from './../../../../http/models/general.model';
import { DataService } from './../../../../storage/data.service';
import { IAddress, IRegister } from './../../../../http/models/register.model';
import { ToastrService } from 'ngx-toastr';
import { Component, OnInit, ViewChild } from '@angular/core';
import { FormControl, Validators, FormGroup } from '@angular/forms';
import { ModalDirective } from 'angular-bootstrap-md';
import { Router } from '@angular/router';
import { NicValidator } from '../../../../directive/validator/compare.directive';
import { AppLoadService } from 'src/app/http/shared/app-load.service';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-non-sri-lankan',
  templateUrl: './non-sri-lankan.component.html',
  styleUrls: ['./non-sri-lankan.component.scss']
})
export class NonSriLankanComponent implements OnInit {
  @ViewChild('registerframe') modal: ModalDirective;

  public formGroup: FormGroup;
  pasportSection = false;
  foreignAddress = false;
  isLoginError: boolean;
  public rsData: Array<any> = [];
  public avaterPath: string;
  // Default Avater
  imageUrl = 'assets/images/avatar/user-avatar.png';
  public fileToUpload: File;
  public fcountry: Array<ICountry> = [];
  constructor(
    private router: Router,
    public data: DataService,
    private snotifyService: ToastrService,
    private AppLoad: AppLoadService,
    private spinner: NgxSpinnerService,
  ) { }

  ngOnInit() {
    this.spinner.show();
    this.AppLoad.getCountry()
      .subscribe(
        req => {
          this.fcountry = req['countries'];
          this.spinner.hide();
        }
      );
    this.__validation();
  }

  __validation() {
    this.formGroup = new FormGroup({
      'title': new FormControl('', [
        Validators.required
      ]),
      'firstname': new FormControl('', [
        Validators.required,
        Validators.pattern('[a-zA-Z ]*')
      ]),
      'lastname': new FormControl('', [
        Validators.required,
        Validators.pattern('[a-zA-Z ]*')
      ]),
      'otherName': new FormControl(
        null,
        [Validators.pattern('[a-zA-Z ]*')]
      ),
      'avaterFile': new FormControl(
        null
      ),
      'nic': new FormControl(null,   NicValidator('nic')),
      'passportid': new FormControl(null, [Validators.required, Validators.pattern('[0-9a-zA-Z]+')]),
      'passportIssueCountry': new FormControl(null, Validators.required),
      'occupation': new FormControl(null),
      'mobileNumber': new FormControl(null, [Validators.required]),
      'telephoneNumber': new FormControl(null),
      'address01': new FormControl(null),
      'address02': new FormControl(null),
      'gndivision': new FormControl(null),
      'city': new FormControl(null),
      'district': new FormControl(null),
      'province': new FormControl(null),
      'country': new FormControl(null),
      'postCode': new FormControl(null),
      'frAddress01': new FormControl(null, [Validators.required]),
      'frAddress02': new FormControl(null, [Validators.required]),
      'frCity': new FormControl(null, [Validators.required]),
      'frProvince': new FormControl(null, [Validators.required]),
      'frCountry': new FormControl(null, [Validators.required]),
      'frPostCode': new FormControl(null, [Validators.required]),
    });
    this.country.setValue('Sri Lanka');
  }

  get title() { return this.formGroup.get('title'); }
  get firstname() { return this.formGroup.get('firstname'); }
  get lastname() { return this.formGroup.get('lastname'); }
  get otherName() { return this.formGroup.get('otherName'); }
  get avaterFile() { return this.formGroup.get('avaterFile'); }
  get nic() { return this.formGroup.get('nic'); }
  get passportid() { return this.formGroup.get('passportid'); }
  get passportIssueCountry() { return this.formGroup.get('passportIssueCountry'); }
  get occupation() { return this.formGroup.get('occupation'); }
  get mobileNumber() { return this.formGroup.get('mobileNumber'); }
  get telephoneNumber() { return this.formGroup.get('telephoneNumber'); }
  get address01() { return this.formGroup.get('address01'); }
  get address02() { return this.formGroup.get('address02'); }
  get city() { return this.formGroup.get('city'); }
  get district() { return this.formGroup.get('district'); }
  get province() { return this.formGroup.get('province'); }
  get country() { return this.formGroup.get('country'); }
  get gndivision() { return this.formGroup.get('gndivision'); }
  get postCode() { return this.formGroup.get('postCode'); }
  get frAddress01() { return this.formGroup.get('frAddress01'); }
  get frAddress02() { return this.formGroup.get('frAddress02'); }
  get frCity() { return this.formGroup.get('frCity'); }
  get frProvince() { return this.formGroup.get('frProvince'); }
  get frCountry() { return this.formGroup.get('frCountry'); }
  get frPostCode() { return this.formGroup.get('frPostCode'); }

  showModal() {
    this.modal.show();
  }

  onSubmit(): void {

    if (!this.formGroup.valid) {
      return;
    }

    const registerData: IRegister = {
      title: this.title.value,
      firstname: this.firstname.value,
      lastname: this.lastname.value,
      otherName: this.otherName.value,
      avater: this.fileToUpload,
      nic: this.nic.value,
      passportid: this.passportid.value,
      passportIssueCountry: this.passportIssueCountry.value,
      occupation: this.occupation.value,
      mobileNumber: this.mobileNumber.value,
      telephoneNumber: this.telephoneNumber.value,
      isSrilanka: 'no'
    };

    const addressData: Array<IAddress> = [
      {
        address01: this.address01.value,
        address02: this.address02.value,
        gndivision: this.gndivision.value  === null ? '' : this.gndivision.value,
        city: this.city.value === null ? '' : this.city.value.description_en,
        district: this.district.value === null ? '' : this.district.value.description_en,
        province: this.province.value === null ? '' : this.province.value.description_en,
        country: this.country.value,
        postCode: this.postCode.value
      },
      {
        address01: this.frAddress01.value,
        address02: this.frAddress02.value,
        city: this.frCity.value,
        district: '',
        province: this.frProvince.value,
        country: this.frCountry.value,
        postCode: this.frPostCode.value
      }
    ];

    this.data.regData = {
      details: registerData,
      address: addressData
    };

    this.router.navigate(['credential']);
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
}
