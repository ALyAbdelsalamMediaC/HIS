@extends('layouts.app')
@section('title', 'HIS | Help Center')
@section('content')

  <section>
    <div class="d-flex justify-content-between align-items-center">
    <div class="gap-3 d-flex align-items-center">
      <a href="{{ url()->previous() }}" class="arrow-back-btn">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
      </a>

      <div>
      <h2 class="h2-semibold" style="color:#35758C;">Help Center</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Your guide to seamless assistance </p>
      </div>
    </div>

    <div class="gap-2 d-flex align-items-center">
      @if(auth()->user()->hasRole('admin'))
      <x-button type="button" data-bs-toggle="modal" data-bs-target="#editCategoriesModal" class="me-2">
      <x-svg-icon name="edit-pen2" size="16" />
      Edit Categories
      </x-button>

      <x-link_btn href="{{  route('policies.create') }}">
      <x-svg-icon name="plus3" size="20" />
      <span>Add new help</span>
      </x-link_btn>
      @endif
    </div>

    </div>

    <div class="accordion" id="categoriesAccordion" style="margin-top: 15px;">
    @forelse ($categories as $category)
    <div class="mb-3 accordion-item">
      <h2 class="accordion-header d-flex" id="categoryHeading{{ $category->id }}">
      <button type="button" class="p-0 border-0 btn btn-link delete-category-btn ms-2" data-bs-toggle="modal"
      data-bs-target="#deleteCategoryModal{{ $category->id }}">
      @if(auth()->user()->hasRole('admin'))
      <x-svg-icon name="trash" size="18" color="#adadad" />
      @endif
      </button>
      <button class="accordion-button h3-semibold" type="button" data-bs-toggle="collapse"
      data-bs-target="#categoryCollapse{{ $category->id }}" aria-expanded="true"
      aria-controls="categoryCollapse{{ $category->id }}">
      {{ $category->title }}
      </button>
      </h2>
      <div id="categoryCollapse{{ $category->id }}" class="accordion-collapse collapse"
      aria-labelledby="categoryHeading{{ $category->id }}" data-bs-parent="#categoriesAccordion">
      <div class="accordion-body">
      @if($category->policies->count() > 0)
      <div class="accordion" id="policyAccordion{{ $category->id }}">
      @foreach($category->policies as $policy)
      <div class="mb-2 accordion-item">
      <h2 class="accordion-header d-flex" id="policyHeading{{ $policy->id }}">
      <button type="button" class="p-0 border-0 btn btn-link delete-policy-btn ms-1" data-bs-toggle="modal"
      data-bs-target="#deletePolicyModal{{ $policy->id }}">
      @if(auth()->user()->hasRole('admin'))
      <x-svg-icon name="trash" size="18" color="#adadad" />
      @endif
      </button>
      <button class="accordion-button h3-regular" type="button" data-bs-toggle="collapse"
      data-bs-target="#policyCollapse{{ $policy->id }}" aria-expanded="true"
      aria-controls="policyCollapse{{ $policy->id }}">
      {{ $policy->title }}
      <a href="{{ route('policies.edit', $policy) }}">
      @if(auth()->user()->hasRole('admin'))
      <x-svg-icon class="mx-2" name="edit-pen2" size="18" color="#adadad" />
      @endif
      </a>
      </button>
      </h2>
      <div id="policyCollapse{{ $policy->id }}" class="accordion-collapse collapse"
      aria-labelledby="policyHeading{{ $policy->id }}" data-bs-parent="#policyAccordion{{ $category->id }}">
      <div class="accordion-body">
      <p>{{ $policy->body }}</p>
      <p class="mt-2 text-muted small">
      Added by: {{ $policy->addedBy->name }} on {{ $policy->created_at ? $policy->created_at->format('M d, Y') : 'N/A' }}
      </p>
      </div>
      </div>
      </div>
      <!-- Delete Modal for Policy -->
      <x-modal id="deletePolicyModal{{ $policy->id }}" title="Delete Help">
      @if(auth()->user()->hasRole('admin'))
      <div class="my-3">
      <p class="h3-semibold" style="color:black;">Are you sure you want to delete the help
      "{{ $policy->title }}"?</p>
      </div>
      <div class="modal-footer">
      <x-button type="button" class="px-4 bg-trans-btn" data-bs-dismiss="modal">Cancel</x-button>
      <form action="{{ route('policies.destroy', $policy->id) }}" method="POST">
      @csrf
      @method('DELETE')
      <x-button type="submit" class="px-4 btn-danger">Delete</x-button>
      </form>
      </div>
      @endif
      </x-modal>
      @endforeach
      </div>
      @else
      <p>No help items in this category.</p>
      @endif
      </div>
      </div>
    </div>
    <!-- Delete Modal for Category -->
    <x-modal id="deleteCategoryModal{{ $category->id }}" title="Delete Category">
      @if(auth()->user()->hasRole('admin'))
      <div class="my-3">
      <p class="h3-semibold" style="color:black;">Are you sure you want to delete the category
      "{{ $category->title }}"?</p>
      </div>
      <div class="modal-footer">
      <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;"
      data-bs-dismiss="modal">Cancel</x-button>
      <form action="{{ route('policies.categories.destroy', $category) }}" method="POST">
      @csrf
      @method('DELETE')
      <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
      </form>
      </div>
      @endif
    </x-modal>
    @empty
    <p>No categories found.</p>
    @endforelse
    </div>

    <!-- Edit Categories Modal -->
    <x-modal id="editCategoriesModal" title="Edit Categories">
    @if(auth()->user()->hasRole('admin'))
    <div class="categories-list">
      @forelse($categories as $category)
      <div class="p-2 category-item d-flex align-items-center justify-content-between border-bottom">
      <div class="category-title d-flex align-items-center flex-grow-1" data-id="{{ $category->id }}">
      <span class="category-text">{{ $category->title }}</span>
      <form action="{{ route('policies.categories.update', $category) }}" method="POST"
      class="edit-category-form d-none w-100">
      @csrf
      @method('PUT')
      <div class="d-flex">
        <x-text_input type="text" name="title" placeholder="category title" class="category-input"
        value="{{ $category->title }}" />
      </div>
      </form>
      </div>
      <div class="mx-2 category-actions">
      <button type="button" class="p-0 border-0 btn btn-link edit-category-btn me-2" data-id="{{ $category->id }}">
      <x-svg-icon name="edit-pen2" size="18" color="#adadad" />
      </button>
      </div>
      </div>
    @empty
      <p class="p-3 text-center">No categories found.</p>
    @endforelse
    </div>
    <div class="mt-3 d-flex justify-content-end">
      <x-button type="button" class="bg-trans-btn" data-bs-dismiss="modal">Close</x-button>
    </div>
    @endif
    </x-modal>
  </section>

@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
    // Handle edit button clicks
    const editButtons = document.querySelectorAll('.edit-category-btn');
    editButtons.forEach(button => {
      button.addEventListener('click', function () {
      const categoryId = this.getAttribute('data-id');
      const categoryItem = document.querySelector(`.category-title[data-id="${categoryId}"]`);
      const textElement = categoryItem.querySelector('.category-text');
      const formElement = categoryItem.querySelector('.edit-category-form');

      // Hide text, show form
      textElement.classList.add('d-none');
      formElement.classList.remove('d-none');

      // Focus on input
      const inputElement = formElement.querySelector('.category-input');
      inputElement.focus();
      inputElement.select();

      // Add event listener for enter key to submit
      inputElement.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
        e.preventDefault();
        formElement.submit();
        } else if (e.key === 'Escape') {
        textElement.classList.remove('d-none');
        formElement.classList.add('d-none');
        }
      });
      });
    });
    });
  </script>
@endpush