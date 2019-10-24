import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NameCheckNameWithReSubmiteSocietyComponent } from './name-check-name-with-re-submite-society.component';

describe('NameCheckNameWithReSubmiteSocietyComponent', () => {
  let component: NameCheckNameWithReSubmiteSocietyComponent;
  let fixture: ComponentFixture<NameCheckNameWithReSubmiteSocietyComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NameCheckNameWithReSubmiteSocietyComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NameCheckNameWithReSubmiteSocietyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
