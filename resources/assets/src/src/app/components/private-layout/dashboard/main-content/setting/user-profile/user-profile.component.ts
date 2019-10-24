import { NgxSpinnerService } from 'ngx-spinner';
import { DataService } from './../../../../../../storage/data.service';
import { IAddress } from './../../../../../../http/models/register.model';
import { GlobleUserService } from './../../../../../../http/shared/globle.user.service';
import { FormGroup, FormControl, Validators } from '@angular/forms';
import { UserService } from './../../../../../../http/services/user.service';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-user-profile',
  templateUrl: './user-profile.component.html',
  styleUrls: ['./user-profile.component.scss']
})
export class UserProfileComponent implements OnInit {

  public fileToUpload: File;
  public updateUser: FormGroup;
  private localAddressId: string;
  private peopleId: string;
  public hideAndShow: boolean;

  constructor(
    public user: GlobleUserService,
    public userAPI: UserService,
    public data: DataService,
    private spinner: NgxSpinnerService) { }

  ngOnInit() {
    this.updateUser = new FormGroup({
      'avaterFile': new FormControl(''),
      'title': new FormControl(this.user.getTitle),
      'firstName': new FormControl(this.user.getFirstName),
      'lastName': new FormControl(this.user.getLastName),
      'otherName': new FormControl(this.user.getOtherName),
      'mobileNumber': new FormControl(this.user.getMobile),
      'telephoneNumber': new FormControl(this.user.getTelephone),
      'occupation': new FormControl(this.user.getOccupation),
      'addressLine01': new FormControl(this.user.getAddress01),
      'addressLine02': new FormControl(this.user.getAddress02),
      'city': new FormControl(this.user.getCity),
      'district': new FormControl(this.user.getDistrict),
      'province': new FormControl(this.user.getProvince),
      'gndivision': new FormControl(this.user.getGNDivision),
      'country': new FormControl(this.user.getCountry),
      'postcode': new FormControl(this.user.getPostCode)
    });

    this.localAddressId = this.user.getAddressId;
    this.peopleId = this.user.getPeopleId;
  }

  get avaterFile() { return this.updateUser.get('avaterFile'); }

  get title() { return this.updateUser.get('title'); }

  get firstName() { return this.updateUser.get('firstName'); }

  get lastName() { return this.updateUser.get('lastName'); }

  get otherName() { return this.updateUser.get('otherName'); }

  get mobileNumber() { return this.updateUser.get('mobileNumber'); }

  get telephoneNumber() { return this.updateUser.get('telephoneNumber'); }

  get occupation() { return this.updateUser.get('occupation'); }

  get addressLine01() { return this.updateUser.get('addressLine01'); }

  get addressLine02() { return this.updateUser.get('addressLine02'); }

  get city() { return this.updateUser.get('city'); }

  get district() { return this.updateUser.get('district'); }

  get province() { return this.updateUser.get('province'); }

  get gndivision() { return this.updateUser.get('gndivision'); }

  get country() { return this.updateUser.get('country'); }

  get postcode() { return this.updateUser.get('postcode'); }

  edit() {
    return this.hideAndShow = !this.hideAndShow;
  }

  onFileInput(fileInput: any, ): void {
    if (fileInput.target.files && fileInput.target.files[0]) {
      const reader = new FileReader();
      this.fileToUpload = fileInput.target.files[0];
      reader.onload = (event: any) => {
        this.user.image = event.target.result;
      };
      reader.readAsDataURL(fileInput.target.files[0]);
    }
  }

  onSubmit() {

    this.spinner.show();
    const mainInfor: any = {
      people: this.peopleId,
      title: this.title.value,
      firstName: this.firstName.value,
      lastName: this.lastName.value,
      otherName: this.otherName.value,
      mobileNumber: this.mobileNumber.value,
      telephoneNumber: this.telephoneNumber.value,
      occupation: this.occupation.value
    };

    const addressData: any = [
      {
        id: this.localAddressId,
        address01: this.addressLine01.value,
        address02: this.addressLine02.value,
        gndivision: this.gndivision.value,
        city: this.city.value != null ? this.city.value  : this.city.value.description_en,
        district: this.district.value != null ? this.district.value : this.district.value.description_en,
        province: this.province.value != null ? this.province.value : this.province.value.description_en,
        country: this.country.value,
        postCode: this.postcode.value
      },
    ];

    const data: any = {
      details: mainInfor,
      address: addressData
    };
    const main_form: FormData = new FormData();
    main_form.append('avater', this.fileToUpload);

    main_form.append('Info', JSON.stringify(data));

    this.userAPI.setUserData(main_form)
      .subscribe(
        req => {
          this.user.setUserData();
          this.user.setAvater();
          this.edit();
          this.spinner.hide();
        }, error => {
          this.spinner.hide();
        }
      );
  }

}
