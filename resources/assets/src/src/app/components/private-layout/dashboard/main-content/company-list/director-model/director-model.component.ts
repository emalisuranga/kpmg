import { Component, OnInit, ViewChild } from '@angular/core';
import { ModalDirective } from 'angular-bootstrap-md';
import { NgxSpinnerService } from 'ngx-spinner';
import swal from 'sweetalert2';
import { AuthService } from 'src/app/http/shared/auth.service';
import { HelperService } from 'src/app/http/shared/helper.service';
import { NameChangeService } from 'src/app/http/services/name-change.service';
import { GeneralService } from 'src/app/http/services/general.service';
import { IMember } from 'src/app/http/models/general.model';
import { FormGroup, FormControl, Validators } from '@angular/forms';
import { ReductionCapitalService } from 'src/app/http/services/reduction-capital.service';

@Component({
  selector: 'app-director-model',
  templateUrl: './director-model.component.html',
  styleUrls: ['./director-model.component.scss']
})
export class DirectorModelComponent implements OnInit {
  @ViewChild('frame') modal: ModalDirective;
  public member: IMember;
  public enable: boolean = false;
  public oldId: string;
  public newID: string;
  public type: string;
  public ResNumber: Boolean = false;

  public date: Date = new Date();
  public min = new Date(2018, 1, 12, 10, 30);

  public max = new Date(this.date.getUTCFullYear(), this.date.getUTCMonth(), this.date.getUTCDate(), this.date.getUTCHours(), this.date.getUTCMinutes());

  formGroup = new FormGroup({
    selectCat: new FormControl(null, [Validators.required]),
  });

  constructor(
    private spinner: NgxSpinnerService,
    private auth: AuthService,
    private details: NameChangeService,
    private helper: HelperService,
    private general: GeneralService,
    private reductionCapital: ReductionCapitalService
  ) { }

  ngOnInit() {

  }

  show(id: string, oldId: string, type: string, nameCreated: any , nameExpired: any) {
    this.oldId = oldId;
    this.newID = id;
    this.type = type;


    if (this.type === 'NAME') {
      this.ResNumber = true;

      if (undefined !== nameCreated && nameCreated) {
        console.log(nameCreated);
        nameCreated = new Date(nameCreated);
        nameCreated = nameCreated.getTime();
        nameCreated = nameCreated - 24 * 60 * 60 * 1000;

        console.log(new Date(nameCreated).toString());
        this.min = new Date(nameCreated);

      }

      if (undefined !== nameExpired && nameExpired) {
      //  nameExpired = new Date(nameCreated);

         let today  = new Date();
         let todayMilisecond = today.getTime();

         nameExpired = (nameExpired > todayMilisecond ) ? todayMilisecond : nameExpired;

         console.log(new Date(nameExpired).toString());
         this.max = new Date(nameExpired);


      }

      // this.min = undefined !== nameCreated && nameCreated  ? new Date(nameCreated) : this.min;
      // this.max = undefined !== nameExpired && nameExpired ? new Date(nameExpired) : this.max;

    }

    this.general.getMember(oldId).subscribe(
      req => {
        if (req['status'] === true) {
          this.enable = true;
          this.member = req['data'];
        }
      }
    );
    if (this.type === 'NAME') {
      this.formGroup = new FormGroup({
        selectCat: new FormControl(null, [Validators.required]),
        resolutiondate: new FormControl(null, [Validators.required]),
      });
    } else {
      this.formGroup = new FormGroup({
        selectCat: new FormControl(null, [Validators.required]),
      });
    }
    this.modal.show();
  }

  get f() { return this.formGroup.controls; }

  onSubmit() {
    var selectDate = null;
    if (this.type === 'NAME') {
      if (this.f.resolutiondate.value !== null) {
        selectDate = this.formatDate(this.f.resolutiondate.value['_d']);
      }
    }

    swal({
      text: 'Please download the form and upload after placing your signature in the signature column using the upload option',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ok'
    }).then((result) => {
      if (result.value) {
        this.spinner.show();

        if (this.type === 'NAME') {
          var data: any = {
            email: this.auth.getEmail(),
            dirId: this.f.selectCat.value,
            oldRefid: this.oldId,
            newRefid: this.newID,
            resolutiondate: selectDate
          };

          this.details.getChangeNameFormFill(data).subscribe(
            req => {
              this.helper.download(req, '', 'form3.pdf');
              this.spinner.hide();
              this.modal.hide();
            }, error => {
              this.spinner.hide();
            }
          );
        }

        if (this.type === 'CAPITAL') {
          var data: any = {
            email: this.auth.getEmail(),
            dirId: this.f.selectCat.value,
            oldRefid: this.oldId,
            newRefid: this.newID
          };
          console.log(data);
          this.reductionCapital.getCapitalFormFill(data).subscribe(
            req => {
              this.helper.download(req, '', 'form8.pdf');
              this.spinner.hide();
              this.modal.hide();
            }, error => {
              this.spinner.hide();
            }
          );
        }

      }
    });
  }

  formatDate(date) {
    var d = new Date(date);
    var month = '' + (d.getMonth() + 1);
    var day = '' + d.getDate();
    var year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month.toString();
    }
    if (day.length < 2) {
      day = '0' + day.toString();
    }

    return [year, month, day].join('-');
  }

}
