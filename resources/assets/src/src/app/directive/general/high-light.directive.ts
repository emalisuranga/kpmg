import { Directive, Input, ElementRef, OnInit } from '@angular/core';

@Directive({
  selector: '[appHighlight]'
})
export class HighLightDirective implements OnInit {

  constructor(private el: ElementRef) { }
  // tslint:disable-next-line:no-input-rename
  @Input('appHighlight') highlightColor: string;

  ngOnInit(): void {
    this.el.nativeElement.style.backgroundColor = this.statusColorCode(this.highlightColor);
  }

  private statusColorCode(status: string) {
    let color = '';
    switch (status) {
      case 'COMPANY_NAME_PENDING':
        color = '#FF9800';
        break;
      case 'COMPANY_NAME_RECOMMEND_FOR_APPROVAL':
        color = '#FF9800';
        break;
      case 'COMPANY_NAME_NOT_RECOMMEND_FOR_APPROVAL':
        color = '#FF9800';
        break;
      case 'COMPANY_NAME_REQUEST_TO_RESUBMIT':
        color = '#3F51B5';
        break;
      case 'COMPANY_NAME_RESUBMITTED':
        color = '#FF9800';
        break;
      case 'COMPANY_NAME_APPROVED':
        color = '#4CAF50';
        break;
      case 'COMPANY_NAME_REJECTED':
        color = '#F44336';
        break;
      case 'COMPANY_STATUS_PENDING':
        color = '#FF9800';
        break;
      case 'COMPANY_STATUS_RECOMMEND_FOR_APPROVAL':
        color = '#FF9800';
        break;
      case 'COMPANY_STATUS_NOT_RECOMMEND_FOR_APPROVAL':
        color = '#FF9800';
        break;
      case 'COMPANY_STATUS_REQUEST_TO_RESUBMIT':
        color = '#3F51B5';
        break;
      case 'COMPANY_STATUS_RESUBMITTED':
        color = '#FF9800';
        break;
      case 'COMPANY_STATUS_APPROVED':
        color = '#4CAF50';
        break;
      case 'COMPANY_STATUS_REJECTED':
        color = '#F44336';
        break;

      case 'DOCUMENT_PENDING':
        color = '#FF9800';
        break;
      case 'DOCUMENT_APPROVED':
        color = '#FF9800';
        break;
      case 'DOCUMENT_REQUEST_TO_RESUBMIT':
        color = '#F44336';
        break;
      case 'DOCUMENT_REQUESTED':
        color = '#FF9800';
        break;
      case 'DOCUMENT_UPLOADED':
        color = '#FF9800';
        break;
      default:
        color = '#FF9800';
        break;
    }

    return color;
  }
}
