import { GeneralService } from './../../../../http/services/general.service';
import { ICancelReason } from './../../../../http/models/cancel-reason';
import { FormGroup, FormBuilder, FormControl, Validators } from '@angular/forms';
import swal from 'sweetalert2';
import { NgxSpinnerService } from 'ngx-spinner';
import { NameResarvationService } from './../../../../http/services/name-resarvation.service';
import { ModalDirective } from 'angular-bootstrap-md';
import { Component, OnInit, ViewChild, EventEmitter, Output } from '@angular/core';

@Component({
  selector: 'app-name-cancel-box',
  templateUrl: './name-cancel-box.component.html',
  styleUrls: ['./name-cancel-box.component.scss']
})
export class NameCancelBoxComponent implements OnInit {
  @ViewChild('frame') modal: ModalDirective;
  public formGroup: FormGroup;
  @Output() getRefreshList = new EventEmitter();
  id: string;
  clText: string;

  public reasons: Array<ICancelReason> = [
    { id: 1,  description: 'Change of company secretary' },
    { id: 2,  description: 'Decided Change the type of company' },
    { id: 3,  description: 'Decided not to incorporate this company name' },
    { id: 4,  description: 'Other' }
  ];

  constructor(
    public rec: NameResarvationService,
    private formBuilder: FormBuilder,
    private spinner: NgxSpinnerService,
  ) { }

  ngOnInit() {
    // this.getReason();
    this.formGroup = this.formBuilder.group({
      clienText: new FormControl(null),
      rdcancel: new FormControl(null, Validators.required)
    });
  }

  // getReason() {
  //   this.general.getCancelReason()
  //   .subscribe(reasons => {
  //     console.log(reasons);

  //   });
  // }

  get rdcancel() { return this.formGroup.get('rdcancel'); }

  get clienText() { return this.formGroup.get('clienText'); }

  showModel(id: string) {
    this.id = id;
    this.modal.show();
  }

  onSubmit() {
    this.rdcancel.value !== 'Other' ? this.clText = this.rdcancel.value : this.clText =  this.clienText.value;

    swal({
      title: 'Are you sure?',
      text: 'You won\'t be able to revert this!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.rec.nameCancel(this.id, this.clText)
          .subscribe(
            req => {
              if (req['success'] === 'success') {
                this.getRefreshList.emit();
                swal(
                  'Deleted!',
                  'Your process has been Success!',
                  'success'
                );
              }else{
                swal(
                  'Delete!',
                  'Your process has been Unsuccess!',
                  'success'
                );
              }
            }, error => {
              this.spinner.hide();
            }
          );
      }
    });
  }

}
